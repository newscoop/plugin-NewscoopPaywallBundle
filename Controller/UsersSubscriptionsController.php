<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;
use Newscoop\Entity\User;
use Newscoop\PaywallBundle\Events\PaywallEvents;

class UsersSubscriptionsController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/users-subscriptions", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm('subscriptionaddForm');

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/load-subscriptions", options={"expose"=true})
     * @Template()
     */
    public function loadSubscriptionsAction(Request $request)
    {
        $cacheService = $this->get('newscoop.cache');
        $subscriptionService = $this->get('paywall.subscription.service');
        $criteria = $this->processRequest($request);
        $userSubscriptions = $this->get('paywall.subscription.service')->getListByCriteria($criteria);

        $pocessed = array();
        foreach ($userSubscriptions as $subscription) {
            $pocessed[] = $this->processSubscription($subscription);
        }

        $responseArray = array(
            'iTotalRecords' => $userSubscriptions->count,
            'iTotalDisplayRecords' => $request->get('sSearch') ? count($pocessed) : $userSubscriptions->count,
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
            'topay' => $userSubscription['toPay'],
            'currency' => $userSubscription['currency'],
            'type' => $userSubscription['type'],
            'active' => $userSubscription['active'],
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
            $this->dispatchNotificationEvent(PaywallEvents::SUBSCRIPTION_STATUS_CHANGE, $subscription);
            $subscriptionService->deleteById($id);

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
     * @Route("/admin/paywall_plugin/users-subscriptions/add-subscription", options={"expose"=true})
     */
    public function addSubscriptionAction(Request $request)
    {
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $subscription = $subscriptionService->create();
        $form = $this->createForm('subscriptionaddForm');
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $subscriptionConfig = $subscriptionService->getOneSubscriptionSpecification($data['subscriptions']);

                $subscriptionData = new \Newscoop\PaywallBundle\Subscription\SubscriptionData(array(
                    'userId' => $data['users'],
                    'subscriptionId' => $subscriptionConfig->getSubscription(),
                    'publicationId' => $subscriptionConfig->getPublication(),
                    'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                    'days' => $subscriptionConfig->getSubscription()->getRange(),
                    'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                    'type' => $data['type'],
                    'active' => $data['status'] === 'Y' ? true : false,
                ), $subscription);

                $subscription = $subscriptionService->update($subscription, $subscriptionData);
                $subscription->setExpireAt($subscriptionService->getExpirationDate($subscription));
                $subscriptionService->save($subscription);

                $this->dispatchNotificationEvent(PaywallEvents::ADMIN_ORDER_SUBSCRIPTION, $subscription);

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_index'));
            }
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/getusers", options={"expose"=true})
     */
    public function getUsersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('Newscoop\Entity\User')->createQueryBuilder('u');
        $qb->select('u.id', 'u.username');
        $qb->andWhere('u.status = :status')
            ->setParameter('status', User::STATUS_ACTIVE);
        $qb->andWhere("(u.username LIKE :query)");
        $qb->setParameter('query', '%'.trim($request->get('term'), '%').'%');
        $qb->setMaxResults(25);
        $qb->orderBy('u.username', 'asc');

        return new JsonResponse($qb->getQuery()->getArrayResult());
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{id}", options={"expose"=true})
     * @Template()
     */
    public function editsubscriptionAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $this->get('paywall.subscription.service')->getOneById($id);

        $form = $this->createForm('subscriptioneditForm', $subscription);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_index'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => new \MetaUser($subscription->getUser()),
            'avatarHash' => md5($subscription->getUser()->getEmail()),
            'subscription' => $subscription,
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/get-subscription-details", options={"expose"=true})
     */
    public function getSubscriptionDetailsAjaxAction(Request $request)
    {
        return new Response(json_encode($this->get('paywall.subscription.service')->getSubscriptionDetails($request->get('subscriptionId'))));
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/exist-check", options={"expose"=true})
     */
    public function existCheckAjaxAction(Request $request)
    {
        $subscription = $this->get('paywall.subscription.service')->getOneByUserAndSubscription($request->get('userId'), $request->get('subscriptionId'));

        $status = false;
        if ($subscription) {
            $status = true;
        }

        return new Response(json_encode(array('status' => $status)));
    }
}
