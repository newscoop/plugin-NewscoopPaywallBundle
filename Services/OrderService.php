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

    public function __construct(
        CurrencyContextInterface $context,
        CurrencyConverterInterface $converter,
        PaywallService $subscriptionService,
        DiscountProcessorInterface $processor
    ) {
        $this->context = $context;
        $this->converter = $converter;
        $this->subscriptionService = $subscriptionService;
        $this->processor = $processor;
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
                );

                if ($item) {
                    if (!$item->isActive()) {
                        continue;
                    }

                    $currentItemPeriod = $item->getDuration();
                        // e.g month === month
                    if ($currentItemPeriod['attribute'] === $newlySelectedPeriod->getAttribute()) {
                        $orderItem = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);
                        $orderItem->setProlonged(true);
                        $orderItem->setParent($item);
                        $orderItem->setCreatedAt($item->getCreatedAt());
                        $orderItem->setStartsAt($item->getExpireAt());
                    } else {
                        continue;
                    }
                }

                $userSubscription = $orderItem;
                if (!$orderItem) {
                    $userSubscription = $this->instantiateOrderItem($subscription, $newlySelectedPeriod);
                }

                if ($newlySelectedPeriod->getDiscount()) {
                    $userSubscription->addDiscount($newlySelectedPeriod->getDiscount());
                }

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
