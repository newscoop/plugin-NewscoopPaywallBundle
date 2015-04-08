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

        $chosenSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneByName($request->get('subscription_name'));

        if (!$chosenSubscription) {
            $response->setContent($templatesService->fetchTemplate("_paywall/error.tpl", array(
                'msg' => $translator->trans('paywall.alert.notexists'),
            )));

            return $response;
        }

        $userSubscription = $subscriptionService->getOneByUserAndSubscription($user->getId(), $chosenSubscription->getId());
        if ($userSubscription) {
            $response->setContent($templatesService->fetchTemplate("_paywall/error.tpl", array(
                'msg' => $translator->trans('paywall.manage.error.exists.subscription'),
            )));

            return $response;
        }

        $specificationArray = $chosenSubscription->getSpecification()->toArray();
        $specification = $specificationArray[0];

        $publication = $em->getReference('Newscoop\Entity\Publication', $specification->getPublication());
        $language = $subscriptionService->getLanguageRepository()->findOneById($request->get('language_id'));
        if (!$language && $publication) {
            $language = $publication->getLanguage();
        }

        $subscription = $subscriptionService->create();
        $subscriptionData = new SubscriptionData(array(
            'userId' => $user,
            //'subscriptionId' => $chosenSubscription->getId(),
            'mainSubscriptionId' => $chosenSubscription,
            'publicationId' => $request->get('publication_id') ?: $publication->getId(),
            'toPay' => $chosenSubscription->getPrice(),
            'days' => $chosenSubscription->getRange(),
            'currency' => $chosenSubscription->getCurrency(),
            'type' => 'T',
            'active' => false,
        ), $subscription);

        switch ($chosenSubscription->getType()) {
            case 'article':
                $article = $subscriptionService->getArticleRepository()->findOneByNumber($request->get('article_id') ?: $specification->getArticle());
                $subscriptionData->addArticle($article, $language);
                break;

            case 'section':
                $section = $subscriptionService->getSectionRepository()->findOneByNumber($request->get('section_id') ?: $specification->getSection());
                $subscriptionData->addSection($section, $language);
                break;

            case 'issue':
                $issue = $subscriptionService->getIssueRepository()->findOneByNumber($request->get('issue_id') ?: $specification->getIssue());
                $subscriptionData->addIssue($issue, $language);
                break;
            default:
                # code...
                break;
        }

        $subscription = $subscriptionService->update($subscription, $subscriptionData);
        $subscriptionService->save($subscription);
        $response->setContent($templatesService->fetchTemplate("_paywall/success.tpl", array('subscription' => $subscription)));

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
