<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\Duration;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use Newscoop\PaywallBundle\Discount\PercentageDiscount;
use Newscoop\PaywallBundle\Discount\DiscountProcessor;

class OrderController extends BaseController
{
    /**
     * @Route("/paywall/subscriptions/order-batch", name="paywall_subscribe_order_batch", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function batchOrderAction(Request $request)
    {
        $em = $this->get('em');
        $items = $request->request->get('batchorder');
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $templatesService = $this->get('newscoop.templates.service');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        $response = new JsonResponse();

        $orderObj = $this->processOrderItems($items);
        $processor = new DiscountProcessor();
        $processedOrder = $processor->process($orderObj, new PercentageDiscount());

        $orderedSubscriptions = array();
        foreach ($orderObj->getItems() as $key => $item) {
            $orderedSubscriptions[] = $item;
            $subscriptionService->save($item);
        }

        $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $orderedSubscriptions);

        $response->setContent($templatesService->fetchTemplate('_paywall/success.tpl', array(
            'subscriptions' => $orderedSubscriptions,
        )));

        return $response;
    }

    /**
     * @Route("/paywall/subscriptions/calculate", name="paywall_subscribe_order_calculate", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function calculateAction(Request $request)
    {
        $em = $this->get('em');
        $items = $request->request->get('batchorder');
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $templatesService = $this->get('newscoop.templates.service');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        $response = new JsonResponse();

        $orderObj = $this->processOrderItems($items);
        $processor = new \Newscoop\PaywallBundle\Discount\DiscountProcessor();
        $processedOrder = $processor->process($orderObj);
        $calculator = new \Newscoop\PaywallBundle\Calculator\DiscountCalculator();

        return new JsonResponse(array(
            'itemsCount' => $processedOrder->countItems(),
            'total' => $calculator->calculate($processedOrder),
        ));
    }

    private function processOrderItems(array $items = array())
    {
        try {
            $em = $this->get('em');
            $userService = $this->get('user');
            $user = $userService->getCurrentUser();
            $subscriptionService = $this->container->get('paywall.subscription.service');
            $orderObj = new \Newscoop\PaywallBundle\Order\Order();

            foreach ($items as $key => $item) {
                if (!$item[0]) {
                    continue;
                }

                $subscription = $em->getReference('Newscoop\PaywallBundle\Entity\Subscriptions', $key);
                $userSubscriptionInactive = $subscriptionService->getOneByUserAndSubscription(
                    $user->getId(),
                    $subscription->getId(),
                    'N'
                );

                $userSubscription = $subscriptionService->getOneByUserAndSubscription(
                    $user->getId(),
                    $subscription->getId()
                );

                if ($userSubscription || $userSubscriptionInactive) {
                    continue;
                }

                $userSubscription = $this->createUserSubscriptionFrom($subscription, $item[0]);
                //$subscriptionService->save($userSubscription, false);
                $orderObj->addItem($userSubscription);
            }

            return $orderObj;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function filterRanges(Subscriptions $subscription, $durationId)
    {
        $ranges = $subscription->getRanges()->filter(function (Duration $duration) use ($durationId) {
            return $duration->getId() == $durationId;
        });

        return $ranges->first();
    }

    private function createUserSubscriptionFrom(Subscriptions $subscription, $durationId)
    {
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();

        $durationObj = $this->filterRanges($subscription, $durationId);
        $duration = array(
            'value' => $durationObj->getValue(),
            'attribute' => $durationObj->getAttribute(),
        );

        $discount = array();
        if ($durationObj->getDiscount()) {
            $discount['value'] = $durationObj->getDiscount()->getValue();
            $discount['type'] = $durationObj->getDiscount()->getType();
        }

        $specification = $subscription->getSpecification()->first();
        $userSubscription = $subscriptionService->create();
        $subscriptionData = new SubscriptionData(array(
            'userId' => $user,
            'subscriptionId' => $subscription,
            'publicationId' => $specification->getPublication(),
            'toPay' => $subscription->getPrice(),
            'duration' => $duration,
            'discount' => $discount,
            'currency' => $subscription->getCurrency(),
            'type' => 'T',
            'active' => false,
        ), $userSubscription);

        return $subscriptionService->update($userSubscription, $subscriptionData);
    }
}
