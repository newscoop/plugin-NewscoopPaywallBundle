<?php

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use Newscoop\PaywallBundle\Events\PaywallEvents;

class DefaultController extends BaseController
{
    /**
     * Show succes page or redirect to one.
     *
     * @Route("/paywall/return/success")
     */
    public function statusSuccessAction()
    {
        return $this->render('NewscoopPaywallBundle:Default:statusSuccess.html.smarty');
    }

    /**
     * Show error page or redirect to one.
     *
     * @Route("/paywall/return/error")
     */
    public function statusErrorAction()
    {
        return $this->render('NewscoopPaywallBundle:Default:statusError.html.smarty');
    }

    /**
     * Show cancel page or redirect to one.
     *
     * @Route("/paywall/return/cancel")
     */
    public function statusCancelAction()
    {
        return $this->render('NewscoopPaywallBundle:Default:statusCancel.html.smarty');
    }

    /**
     * Get callback response from paywall/payment provider and proccess it.
     *
     * @Route("/paywall/subscriptions/add/")
     */
    public function createSubscriptionAction(Request $request)
    {
        $adapter = $this->container->getService('newscoop.paywall.adapter');
        //$adapter->setRequest($request);

        $response = $adapter->purchase(array(
            'amount' => '10.00',
            'currency' => 'PLN',
        ));

        $response->redirect();
    }

    /**
     * Get callback response from paywall/payment provider and proccess it.
     *
     * @Route("/paywall/subscriptions/get", name="paywall_subscribe", options={"expose"=true})
     */
    public function getSubscriptionAction(Request $request)
    {
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        $em = $this->get('em');
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setCharset('utf-8');
        $templatesService = $this->get('newscoop.templates.service');

        $chosenSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
            ->findOneByName($request->get('subscription_name'));

        if (!$chosenSubscription) {
            $response->setContent($templatesService->fetchTemplate('_paywall/error.tpl', array(
                'msg' => $translator->trans('paywall.alert.notexists'),
            )));

            return $response;
        }

        $userSubscriptionInactive = $subscriptionService->getOneByUserAndSubscription($user->getId(), $chosenSubscription->getId(), 'N');
        $userSubscription = $subscriptionService->getOneByUserAndSubscription($user->getId(), $chosenSubscription->getId());
        if ($userSubscription || $userSubscriptionInactive) {
            $response->setContent($templatesService->fetchTemplate('_paywall/error.tpl', array(
                'msg' => $translator->trans('paywall.manage.error.exists.subscription'),
            )));

            return $response;
        }

        $specificationArray = $chosenSubscription->getSpecification()->toArray();
        $specification = $specificationArray[0];
        $subscription = $subscriptionService->create();
        $subscriptionData = new SubscriptionData(array(
            'userId' => $user,
            'subscriptionId' => $chosenSubscription,
            'publicationId' => $request->get('publication_id') ?: $specification->getPublication()->getId(),
            'toPay' => $chosenSubscription->getPrice(),
            'days' => $chosenSubscription->getRange(),
            'currency' => $chosenSubscription->getCurrency(),
            'type' => 'T',
            'active' => false,
        ), $subscription);

        $subscription = $subscriptionService->update($subscription, $subscriptionData);
        $subscriptionService->save($subscription);

        $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $subscription);

        $response->setContent($templatesService->fetchTemplate('_paywall/success.tpl', array(
            'subscription' => $subscription,
        )));

        return $response;
    }

    /**
     * Get callback response from paywall/payment provider and proccess it.
     *
     * @Route("/paywall/return/callback")
     */
    public function callbackAction(Request $request)
    {
        $adapter = $this->container->getService('newscoop.paywall.adapter');
        $adapterResult = $adapter->setRequest($request);
        $adapterResult = $adapter->proccess();

        if (!($adapterResult instanceof Response)) {
            throw new \Exception("Returned value from adapter must be instance of Symfony\Component\HttpFoundation\Response", 1);
        }

        return $adapterResult;
    }
}
