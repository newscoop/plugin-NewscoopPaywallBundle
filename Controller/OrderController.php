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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\Duration;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use Newscoop\PaywallBundle\Discount\DiscountProcessor;
use Newscoop\PaywallBundle\Entity\Order;
use Newscoop\PaywallBundle\Calculator\DiscountCalculator;

class OrderController extends BaseController
{
    /**
     * @Route("/{language}/paywall/subscriptions", name="paywall_subscriptions", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $response->setContent($templatesService->fetchTemplate('_paywall/index.tpl'));

        return $response;
    }

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

        /*$calculator = new DiscountCalculator();
        $order = $this->processOrderItems($items);

        $beforeDiscount = $calculator->calculate($order);*/

        $order = $this->processOrderItems($items);
        $processor = $this->get('newscoop_paywall.processor.discounts');
        //$processor->process($order);
        foreach ($order->getItems() as $item) {
            $processor->process($item);
        }

        $order->calculateTotal();

        //$processor = new DiscountProcessor();
        /*$percentageDiscount = $this->get('newscoop_paywall.discounts.percentage_discount');
        $processedOrder = $processor->process($order, $percentageDiscount);

        $afterDiscount = $calculator->calculate($processedOrder);

        //$order->setDiscountTotal($beforeDiscount - $afterDiscount);
        $order->setTotal($afterDiscount);*/
        $order->setUser($user);
        $order->setCurrency('USD');

        $em->persist($order);
        $em->flush();

        $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $order->getItems()->toArray());

        $response->setStatusCode(204);

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
        $items = $request->request->get('batchorder', array());
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        $response = new JsonResponse();
        $order = $this->processOrderItems($items);
        $processor = $this->get('newscoop_paywall.processor.discounts');
        foreach ($order->getItems() as $item) {
            $processor->process($item);
        }

        $order->calculateTotal();

        return new JsonResponse(array(
            'itemsCount' => $order->countItems(),
            'total' => $order->getTotal(),
        ));
    }

    private function processOrderItems(array $items = array())
    {
        try {
            $em = $this->get('em');
            $userService = $this->get('user');
            $user = $userService->getCurrentUser();
            $subscriptionService = $this->container->get('paywall.subscription.service');
            $order = new Order();
            foreach ($items as $subscriptionId => $periodId) {
                if (!$periodId) {
                    continue;
                }

                $subscription = $em->getReference('Newscoop\PaywallBundle\Entity\Subscriptions', $subscriptionId);
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

                $userSubscription = $this->createUserSubscriptionFrom($subscription, $periodId);
                $userSubscription->setOrder($order);
                $order->addItem($userSubscription);
            }

            return $order;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function createUserSubscriptionFrom(Subscriptions $subscription, $periodId)
    {
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();

        $durationObj = $subscriptionService->filterRanges($subscription, $periodId);
        $duration = array(
            'id' => $durationObj->getId(),
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
