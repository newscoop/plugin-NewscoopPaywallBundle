<?php

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;

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
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $subscriptionsConfig = $subscriptionService->getSubscriptionsConfig();
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        $em = $this->get('em');

        if (!$user) {
            die('not logged in');
        }

        $mainSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneByName($request->get('subscription_name'));

        $choosenSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneByName($request->get('subscription_name'));

        if (!$choosenSubscription) {
            die('subscription doesnt exist');
        }

        $subscription = $subscriptionService->create();
        $subscriptionData = new SubscriptionData(array(
            'userId' => $user,
            //'subscriptionId' => $choosenSubscription->getId(),
            'mainSubscriptionId' => $mainSubscription,
            'publicationId' => $request->get('publication_id'),
            'toPay' => $choosenSubscription->getPrice(),
            'days' => $choosenSubscription->getRange(),
            'currency' => $choosenSubscription->getCurrency(),
            'type' => 'T',
            'active' => false,
        ), $subscription);

        $language = $subscriptionService->getLanguageRepository()->findOneById($request->get('language_id'));
        switch ($choosenSubscription->getType()) {
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

        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $response->setContent($templatesService->fetchTemplate("_paywall/success.tpl", array('sibscription' => $subscription)));
        $response->headers->set('Content-Type', 'text/html');
        $response->setCharset('utf-8');

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
