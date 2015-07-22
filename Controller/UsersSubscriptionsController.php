<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\Order;
use Newscoop\PaywallBundle\Form\Type\OrderItemType;

class UsersSubscriptionsController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/load-subscriptions/{id}", options={"expose"=true})
     * @Template()
     */
    public function loadSubscriptionsAction(Request $request, $id)
    {
        $cacheService = $this->get('newscoop.cache');
        $subscriptionService = $this->get('paywall.subscription.service');
        $criteria = $this->processRequest($request);
        $criteria->order = $id;
        $userSubscriptions = $this->get('paywall.subscription.service')
            ->getUserSubscriptionsByCriteria($criteria, true)
            ->getArrayResult();

        $pocessed = array();
        foreach ($userSubscriptions as $subscription) {
            $pocessed[] = $this->processSubscription($subscription);
        }

        $responseArray = array(
            'iTotalRecords' => count($userSubscriptions),
            'iTotalDisplayRecords' => $request->get('sSearch') ? count($pocessed) : count($userSubscriptions),
            'sEcho' => (int) $request->get('sEcho'),
            'aaData' => $pocessed,
        );

        return new JsonResponse($responseArray);
    }

    private function processSubscription($userSubscription)
    {
        return array(
            'id' => $userSubscription['id'],
            'userid' => $userSubscription['user']['id'],
            'username' => $userSubscription['user']['username'],
            'publication' => $userSubscription['publication']['name'],
            'subscription' => $userSubscription['subscription']['name'],
            'topay' => $userSubscription['toPay'] / $userSubscription['duration']['value'].' '.$userSubscription['currency'],
            'total' => $userSubscription['toPay'].' '.$userSubscription['currency'],
            'period' => $userSubscription['duration']['value'].' '.$userSubscription['duration']['attribute'],
            'currency' => $userSubscription['currency'],
            'type' => $userSubscription['type'],
            'active' => $userSubscription['active'] == 'Y' ? true : false,
            'firstNotify' => $userSubscription['notifySentLevelOne'],
            'secondNotify' => $userSubscription['notifySentLevelTwo'],
            'expiresAt' => $userSubscription['expire_at'],
        );
    }

    private function processRequest($request)
    {
        $criteria = new SubscriptionCriteria();
        if ($request->query->has('sSortDir_0')) {
            $criteria->orderBy[$request->query->get('iSortCol_0')] = $request->query->get('sSortDir_0');
        }

        if ($request->query->has('sSearch')) {
            $criteria->query = $request->query->get('sSearch');
        }

        $criteria->maxResults = $request->query->get('iDisplayLength', 10);
        if ($request->query->has('iDisplayStart')) {
            $criteria->firstResult = $request->query->get('iDisplayStart');
        }

        return $criteria;
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/deactivate/{id}", options={"expose"=true})
     */
    public function deactivateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            try {
                $subscription = $this->get('paywall.subscription.service')->deactivateById($id);
                $this->dispatchNotificationEvent(PaywallEvents::SUBSCRIPTION_STATUS_CHANGE, $subscription);

                return new JsonResponse(array('status' => true));
            } catch (\Exception $exception) {
                return new JsonResponse(array('status' => false));
            }
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/delete/{id}", options={"expose"=true})
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $subscriptionService = $this->get('paywall.subscription.service');
            $subscription = $subscriptionService->deactivateById($id);
            $order = $subscription->getOrder();

            $this->dispatchNotificationEvent(PaywallEvents::SUBSCRIPTION_STATUS_CHANGE, $subscription);
            $em = $this->get('em');
            $subscriptionService->deleteById($id);
            $order->calculateTotal();
            $em->flush();

            return new JsonResponse(array('status' => true));
        } catch (\Exception $exception) {
            return new JsonResponse(array('status' => false));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/activate/{id}", options={"expose"=true})
     */
    public function activateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            try {
                $subscription = $this->get('paywall.subscription.service')->activateById($id);
                $this->dispatchNotificationEvent(PaywallEvents::SUBSCRIPTION_STATUS_CHANGE, $subscription);

                return new JsonResponse(array('status' => true));
            } catch (\Exception $exception) {
                return new JsonResponse(array(
                    'status' => false,
                    'message' => $exception->getMessage(),
                ));
            }
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/add-subscription/{id}", options={"expose"=true})
     */
    public function addSubscriptionAction(Request $request, Order $order)
    {
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $subscription = $subscriptionService->create();
        $em = $this->get('em');
        $form = $this->createForm(new OrderItemType());
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $subscriptionConfig = $subscriptionService->getOneSubscriptionSpecification($data['subscriptions']);
                $userSubscriptionInactive = $subscriptionService->getOneByUserAndSubscription(
                    $order->getUser()->getId(),
                    $subscriptionConfig->getSubscription()->getId(),
                    'N'
                );

                $userSubscription = $subscriptionService->getOneByUserAndSubscription(
                    $order->getUser()->getId(),
                    $subscriptionConfig->getSubscription()->getId()
                );

                if ($userSubscription || $userSubscriptionInactive) {
                    return $this->redirect($this->generateUrl('paywall_plugin_userorder_edit', array(
                        'id' => $order->getId(),
                    )));
                }

                $period = $data['period'];
                $durationObj = $subscriptionService->filterRanges($data['subscriptions'], $period->getId());
                $duration = array(
                    'value' => $durationObj->getValue(),
                    'attribute' => $durationObj->getAttribute(),
                );

                $discount = array();
                if ($durationObj->getDiscount()) {
                    $discount['value'] = $durationObj->getDiscount()->getValue();
                    $discount['type'] = $durationObj->getDiscount()->getType();
                }

                $subscriptionData = new \Newscoop\PaywallBundle\Subscription\SubscriptionData(array(
                    'userId' => $order->getUser(),
                    'duration' => $duration,
                    'discount' => $discount,
                    'subscriptionId' => $subscriptionConfig->getSubscription(),
                    'publicationId' => $subscriptionConfig->getPublication(),
                    'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                    'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                    'type' => $data['type'],
                    'active' => $data['status'] === 'Y' ? true : false,
                ), $userSubscription);

                $subscription->setOrder($order);
                $subscription = $subscriptionService->update($subscription, $subscriptionData);
                if ($data['status'] === 'Y') {
                    $subscription->setExpireAt($subscriptionService->getExpirationDate($subscription));
                }
                $subscriptionService->save($subscription);

                $this->dispatchNotificationEvent(PaywallEvents::ADMIN_ORDER_SUBSCRIPTION, $subscription);

                return $this->redirect($this->generateUrl('paywall_plugin_userorder_edit', array(
                    'id' => $order->getId(),
                )));
            }
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{id}", options={"expose"=true})
     */
    public function editsubscriptionAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $orderItem = $this->get('paywall.subscription.service')->getOneById($id);

        $form = $this->createForm('subscriptioneditForm', $orderItem);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();

                return $this->redirect($this->generateUrl('paywall_plugin_userorder_edit', array(
                    'id' => $orderItem->getOrder()->getId(),
                )));
            }
        }

        return $this->render('NewscoopPaywallBundle:UsersSubscriptions:editsubscription.html.twig', array(
            'form' => $form->createView(),
            'user' => new \MetaUser($orderItem->getUser()),
            'avatarHash' => md5($orderItem->getUser()->getEmail()),
            'subscription' => $orderItem,
        ));
    }
}
