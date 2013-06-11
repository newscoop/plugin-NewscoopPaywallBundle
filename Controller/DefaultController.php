<?php

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Show succes page or redirect to one
     * 
     * @Route("/paywall/return/success")
     */
    public function statusSuccessAction()
    {
        return $this->render('NewscoopPaywallBundle:Default:statusSuccess.html.smarty');
    }

    /**
     * Show error page or redirect to one
     * 
     * @Route("/paywall/return/error")
     */
    public function statusErrorAction()
    {
        return $this->render('NewscoopPaywallBundle:Default:statusError.html.smarty');
    }

    /**
     * Show cancel page or redirect to one
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
     * @Route("/paywall/subscriptions/add/{item}/{number}")
     */
    public function createSubscriptionAction(Request $request, $item, $number)
    {
        $adapter = $this->container->getService('newscoop.paywall.adapter');

    }

    /**
     * Get callback response from paywall/payment provider and proccess it.
     * 
     * @Route("/paywall/subscriptions/get")
     */
    public function getSubscriptionAction(Request $request)
    {
        $subscriptionService = $this->container->get('subscription.service');
        $subscriptionsConfig = $subscriptionService->getSubscriptionsConfig();
        $auth = \Zend_Auth::getInstance();
        $userId = $auth->getIdentity();

        if (array_key_exists($request->get('subscription_name'), $subscriptionsConfig['subscriptions'])) {
            $choosenSubscription = $subscriptionsConfig['subscriptions'][$request->get('subscription_name')];
        } else {
            die('brak subskrypcji');
        }

        $subscription = $subscriptionService->create();
        $subscriptionData = new \Newscoop\Subscription\SubscriptionData(array(
            'userId' => $userId,
            'publicationId' => $request->get('publication_id'),
            'toPay' => $choosenSubscription['price'],
            'days' => $choosenSubscription['range'],
            'currency' => $choosenSubscription['currency']
        ), $subscription);

        $language = $subscriptionService->getLanguageRepository()->findOneById($request->get('language_id'));
        switch ($choosenSubscription['type']) {
            case 'article':
                $article = $subscriptionService->getArticleRepository()->findOneByNumber($request->get('article_id'));
                $subscriptionData->addArticle($article, $language);
                break;
            
            case 'section':
                $section = $subscriptionService->getSectionRepository()->findOneByNumber($request->get('section_id'));
                $subscriptionData->addSection($section, $language);
                break;

            case 'issue':
                $issue = $subscriptionService->getIssueRepository()->findOneByNumber($request->get('issue_id'));
                $subscriptionData->addIssue($issue, $language);
                break;
            default:
                # code...
                break;
        }

        $subscription = $subscriptionService->update($subscription, $subscriptionData);
        $subscriptionService->save($subscription);

        return $this->render('NewscoopPaywallBundle:Default:getSubscription.html.smarty', array(
            'subscriptionId' => $subscription->getId()
        ));
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
