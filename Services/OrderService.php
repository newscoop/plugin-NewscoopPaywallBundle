<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Currency\Converter\CurrencyConverterInterface;
use Newscoop\PaywallBundle\Entity\Order;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use Newscoop\PaywallBundle\Discount\DiscountProcessorInterface;

/**
 * Order service.
 */
class OrderService
{
    protected $context;
    protected $converter;
    protected $subscriptionService;
    protected $processor;
    protected $prolongator;

    public function __construct(
        CurrencyContextInterface $context,
        CurrencyConverterInterface $converter,
        PaywallService $subscriptionService,
        DiscountProcessorInterface $processor,
        ProlongatorInterface $prolongator
    ) {
        $this->context = $context;
        $this->converter = $converter;
        $this->subscriptionService = $subscriptionService;
        $this->processor = $processor;
        $this->prolongator = $prolongator;
    }

    /**
     * Processes and calculates order items.
     * Calculates prices including all discounts.
     *
     * @param array  $items    array of subscription identifiers and its periods
     * @param string $surrency currency
     *
     * @return OrderInterface
     */
    public function processAndCalculateOrderItems(array $items = array(), $currency = null)
    {
        if (null !== $currency) {
            $this->context->setCurrency($currency);
        }

        $order = $this->instantiateOrderItems($items);
        foreach ($order->getItems() as $item) {
            $this->processor->process($item);
        }

        $order->calculateTotal();

        return $order;
    }

    /**
     * Processes and calculates order.
     * Calculates prices, including all discounts.
     *
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function processAndCalculateOrder(OrderInterface $order)
    {
        $order = $this->processor->process($order);
        $order->calculateTotal();

        return $order;
    }

    private function instantiateOrderItems(array $items = array())
    {
        try {
            $order = new Order();
            $order->setCurrency($this->context->getCurrency());
            $order->setUser($this->subscriptionService->getCurrentUser());
            $orderItem = null;

            foreach ($items as $subscriptionId => $periodId) {
                if (!$periodId) {
                    continue;
                }

                $subscription = $this->subscriptionService
                    ->getSubscriptionRepository()
                    ->getReference($subscriptionId);

                $newlySelectedPeriod = $this->subscriptionService->filterRanges($subscription, $periodId);
                $item = $this->subscriptionService->getOrderItemBy(
                    $subscription->getId()
                    //$this->createPeriodArray($newlySelectedPeriod)
                );

                if ($item) {
                    //is subscribed

                    if (!$item->isActive()) {
                        // prolong subscription

                        continue;
                    }
                    //ladybug_dump($item->getId());
                    $currentItemPeriod = $item->getDuration();
                        // e.g month === month
                        if ($currentItemPeriod['attribute'] === $newlySelectedPeriod->getAttribute()) {
                            // e.g 6 months > 3 months
                            //ladybug_dump($newlySelectedPeriod->getValue(), $currentItemPeriod['value']);

                            //if ($newlySelectedPeriod->getValue() != $currentItemPeriod['value']) {
                            //ladybug_dump($newlySelectedPeriod->getDiscount());
                            //die;
                            $orderItem = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);

                            $orderItem->setProlonged(true);
                            //$orderItem->addDiscount($newlySelectedPeriod->getDiscount());
                            $orderItem->setCreatedAt($item->getExpireAt());
                                //$newlySelectedItem = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);
                                //$userSubscription = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);
                                //$userSubscription->setOrder($order);
                                //$order->addItem($userSubscription);
                                //ladybug_dump($order->getItems()->toArray()[0]->getDiscount());
                                //die;
                                //$newlySelectedItem->setOrder($item->getOrder());

                                //ladybug_dump($newlySelectedPeriod->getDiscount()->getValue());
                                //die;
                                //$item->setDuration($this->createPeriodArray($newlySelectedPeriod));

                                //$unitPrice = $this->converter->convert($item->getSubscription()->getPrice(), $item->getCurrency());
                                //$item->setToPay($unitPrice * $newlySelectedPeriod->getValue());

                                //$processedItem = $this->processor->process($item);
                                //$processedItem->setToPay($total * $newlySelectedPeriod->getDiscount()->getValue());
                                //ladybug_dump($processedItem->getToPay());
                                //die;

                                //ladybug_dump();
                                //die;

                                // tak samo zaimplementowac jak DiscountProcess
                                //
                                //$this->prolongator->createRequest($item, $newlySelectedPeriod);

                                //return;
                                //ladybug_dump('order service prolong');
                                //die;
                                // przedluz....
                            //} else {
                             //   continue;
                            //}
                        } else {
                            continue;
                        }
                }

                $userSubscription = $orderItem;
                if (!$orderItem) {
                    //$period = $this->subscriptionService->filterRanges($subscription, $periodId);
                    $userSubscription = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);
                }

                $userSubscription->addDiscount($newlySelectedPeriod->getDiscount());
                $userSubscription->setOrder($order);
                $order->addItem($userSubscription); // if has item, then merge it else add
            }

            return $order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function instantiateOrderItem(Subscriptions $subscription, $period)
    {
        $specification = $subscription->getSpecification()->first();
        $userSubscription = $this->subscriptionService->create();
        $subscriptionData = new SubscriptionData(array(
            'userId' => $this->subscriptionService->getCurrentUser(),
            'subscriptionId' => $subscription,
            'publicationId' => $specification->getPublication(),
            'toPay' => $this->converter->convert($subscription->getPrice(), $this->context->getCurrency()),
            'duration' => $this->createPeriodArray($period),
            'discount' => $this->createDiscountArray($period), // TODO not used remove
            'currency' => $this->context->getCurrency(),
            'type' => 'T',
            'active' => false,
        ), $userSubscription);

        return $this->subscriptionService->update($userSubscription, $subscriptionData);
    }

    private function createDiscountArray($period)
    {
        $discount = array();
        if ($period->getDiscount()) {
            $discount['value'] = $period->getDiscount()->getValue();
            $discount['type'] = $period->getDiscount()->getType();
        }

        return $discount;
    }

    private function createPeriodArray($period)
    {
        $periodArray = array(
            'id' => $period->getId(),
            'value' => $period->getValue(),
            'attribute' => $period->getAttribute(),
        );

        return $periodArray;
    }
}
