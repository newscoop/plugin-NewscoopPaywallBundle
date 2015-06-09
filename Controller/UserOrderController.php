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
use Newscoop\PaywallBundle\Entity\Order;
use Newscoop\PaywallBundle\Entity\UserSubscription;
use Newscoop\PaywallBundle\Form\Type\OrderItemType;
use Newscoop\PaywallBundle\Events\PaywallEvents;

class UserOrderController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/orders", name="paywall_plugin_userorder_index", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $query = $this->getOrderRepository()->findOrders();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('NewscoopPaywallBundle:UserOrder:index.html.twig', array(
            'pagination' => $pagination,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/orders/{id}", name="paywall_plugin_userorder_show", options={"expose"=true})
     */
    public function showAction(Request $request, Order $order)
    {
        return $this->render('NewscoopPaywallBundle:UserOrder:show.html.twig', array(
            'order' => $order,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/orders/edit/{id}", name="paywall_plugin_userorder_edit", options={"expose"=true})
     */
    public function editAction(Request $request, Order $order)
    {
        $form = $this->createForm(new OrderItemType());

        return $this->render('NewscoopPaywallBundle:UserOrder:edit.html.twig', array(
            'order' => $order,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/orders/delete/{id}", options={"expose"=true}, name="paywall_plugin_userorder_delete")
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Order $order)
    {
        $translator = $this->get('translator');
        if ($this->exists($order)) {
            $em = $this->get('em');
            $em->remove($order);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.removed'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.notexists'));
        }

        return $this->redirect($this->generateUrl('paywall_plugin_userorder_index'));
    }

    /**
     * @Route("/admin/paywall_plugin/orders/create/", options={"expose"=true}, name="paywall_plugin_userorder_create")
     *
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        return array();
    }

    /**
     * @Route("/admin/paywall_plugin/orders/{id}/periods/", options={"expose"=true}, name="paywall_plugin_userorder_periods")
     *
     * @Method("POST")
     */
    public function periodsAction(Request $request, Order $order)
    {
        $orderItem = new UserSubscription();
        $form = $this->createForm(new OrderItemType(), $orderItem);
        $form->handleRequest($request);

        return $this->render(
            'NewscoopPaywallBundle:UserOrder:createItem.html.twig',
            array(
                'form' => $form->createView(),
                'orderId' => $order->getId(),
            )
        );
    }

    /**
     * @Route("/admin/paywall_plugin/orders/{id}/item/create/", options={"expose"=true}, name="paywall_plugin_userorder_createitem")
     */
    public function createItemAction(Request $request, Order $order)
    {
        $orderItem = new UserSubscription();
        $form = $this->createForm(new OrderItemType(), $orderItem);
        $em = $this->get('em');
        $translator = $this->get('translator');
        $subscriptionService = $this->container->get('paywall.subscription.service');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $orderItem->setOrder($order);
            $subscription = $orderItem->getSubscription();
            $durationObj = $subscriptionService->filterRanges($subscription, $orderItem->getDuration()->getId());
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
            $subscriptionConfig = $subscriptionService->getOneSubscriptionSpecification($subscription);
            $subscriptionData = new \Newscoop\PaywallBundle\Subscription\SubscriptionData(array(
                    'userId' => $order->getUser(),
                    'duration' => $duration,
                    'discount' => $discount,
                    'subscriptionId' => $subscriptionConfig->getSubscription(),
                    'publicationId' => $subscriptionConfig->getPublication(),
                    'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                    'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                    'type' => $data->getType(),
                    'active' => $data->isActive(),
            ), $orderItem);

            $orderItem = $subscriptionService->update($orderItem, $subscriptionData);
            if ($data->isActive()) {
                $orderItem->setExpireAt($subscriptionService->getExpirationDate($orderItem));
            }

            if ($this->getOrderItemRepository()->checkExistanceInOrder($orderItem) == 0) {
                $subscriptionService->save($orderItem);
                $processor = $this->get('newscoop_paywall.processor.discounts');
                $processor->process($orderItem);
                $order->calculateTotal();
                $em->flush();
                $this->dispatchNotificationEvent(PaywallEvents::ADMIN_ORDER_SUBSCRIPTION, $orderItem);

                $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.created'));
            } else {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.manage.error.exists.subscription'));
            }

            return $this->redirect($this->generateUrl('paywall_plugin_userorder_edit', array(
                'id' => $order->getId(),
            )));
        }

        return $this->render('NewscoopPaywallBundle:UserOrder:createItem.html.twig', array(
            'form' => $form->createView(),
            'orderId' => $order->getId(),
        ));
    }

    private function getOrderItemRepository()
    {
        $em = $this->get('em');

        return $em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription');
    }

    private function exists(Order $order)
    {
        if ($this->getOrderRepository()->findOneById($order->getId())) {
            return true;
        }

        return false;
    }

    private function getOrderRepository()
    {
        $em = $this->get('em');

        return $em->getRepository('Newscoop\PaywallBundle\Entity\Order');
    }
}
