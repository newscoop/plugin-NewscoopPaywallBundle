<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersSubscriptionsController extends Controller
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
            'subscriptions' => $this->get('subscription.service')->getByAll(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/delete/{id}", options={"expose"=true})
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $this->get('subscription.service')->removeById($id);

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/activate/{id}", options={"expose"=true})
     */
    public function activateAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $this->get('subscription.service')->activateById($id);

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/remove/{type}/{id}")
     */
    public function removeAction(Request $request, $type, $id)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $subscription = $this->findByType($em, $type, $id);
            $em->remove($subscription);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/add/{type}", options={"expose"=true})
     */
    public function addAction(Request $request, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $subscriptionService = $this->container->get('subscription.service');
        $subscription = $subscriptionService->getOneById($request->get('subscriptionId'));
        
        $form = $this->createForm('detailsForm');
        if ($request->isMethod('POST')) {
            $form->bind($request);     
            if ($form->isValid()) { 
                $data = $form->getData();
                $subscriptionData = new \Newscoop\Subscription\SubscriptionData(array(
                    'startDate' => $data['startDate'],
                    'paidDays' => $data['paidDays'],
                    'days' => $data['days']
                ), $subscription);

                $language = $subscriptionService->getLanguageRepository()->findOneById($request->get('languageId'));
                switch ($type) {
                    case 'article':
                        $article = $subscriptionService->getArticleRepository()->findOneByNumber($request->get('selectedId'));
                        $subscriptionData->addArticle($article, $language);
                        break;

                    case 'section':
                        $section = $subscriptionService->getSectionRepository()->findOneByNumber($request->get('selectedId'));
                        $subscriptionData->addSection($section, $language);
                        break;

                    case 'issue':
                        $issue = $subscriptionService->getIssueRepository()->findOneByNumber($request->get('selectedId'));
                        $subscriptionData->addIssue($issue, $language);
                        break;

                    default:
                        break;
                }

                $subscription = $subscriptionService->update($subscription, $subscriptionData);
                $subscriptionService->save($subscription);

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_details', 
                    array(
                        'id' => $subscription->getId(), 
                    )
                ));
            }

            return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_details', 
                array(
                    'id' => $subscription->getId(), 
                )
            ));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/add-subscription", options={"expose"=true})
     */
    public function addSubscriptionAction(Request $request)
    {
        $subscriptionService = $this->container->get('subscription.service');
        $subscription = $subscriptionService->create();
        $form = $this->createForm('subscriptionaddForm');
        if ($request->isMethod('POST')) {
            $form->bind($request);     
            if ($form->isValid()) { 
                $data = $form->getData();
                $subscriptionConfig = $subscriptionService->getSubscriptionsConfig($data['subscriptions']);
                $subscriptionData = new \Newscoop\Subscription\SubscriptionData(array(
                    'userId' => $data['users'],
                    'subscriptionId' => $data['subscriptions'],
                    'publicationId' => $subscriptionConfig->getPublication(),
                    'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                    'days' => $subscriptionConfig->getSubscription()->getRange(),
                    'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                    'type' => $data['type'],
                    'active' => $data['status'] === 'Y' ? true : false
                ), $subscription);
                $subscription = $subscriptionService->update($subscription, $subscriptionData);
                $subscriptionService->save($subscription);

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_index'));
            }
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{type}/{id}", options={"expose"=true})
     * @Template()
     */
    public function editAction(Request $request, $type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $this->findByType($em, $type, $id);
            
        $form = $this->createForm('detailsForm', $subscription);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if ($type === 'edit-all-issues') {
                    foreach ($subscription as $issue) {
                        $issue->setStartDate($data['startDate']);
                        $issue->setDays($data['days']);
                        $issue->setPaidDays($data['paidDays']);
                    }
                }

                if ($type === 'edit-all-sections') {
                    foreach ($subscription as $section) {
                        $section->setStartDate($data['startDate']);
                        $section->setDays($data['days']);
                        $section->setPaidDays($data['paidDays']);
                    }
                }

                if ($type === 'edit-all-articles') {
                    foreach ($subscription as $article) {
                        $article->setStartDate($data['startDate']);
                        $article->setDays($data['days']);
                        $article->setPaidDays($data['paidDays']);
                    }
                }

                $em->flush();

                if ($type === 'issue' || $type === 'section' || $type === 'article') {
                    return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_details', 
                        array(
                            'id' => $subscription->getSubscription()->getId(), 
                        )
                    ));
                }

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_details', 
                    array(
                        'id' => $id, 
                    )
                ));
            }
        }
        
        return array(
            'form' => $form->createView(),
            'subscription' => $subscription,
            'type' => $type,
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{id}")
     * @Template()
     */
    public function editsubscriptionAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $this->get('subscription.service')->getOneById($id);

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
            'subscription' => $subscription,
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/details/{id}", options={"expose"=true})
     * @Template()
     */
    public function detailsAction(Request $request, $id)
    {
        $subscriptionService = $this->get('subscription.service');
        $form = $this->createForm('detailsForm');

        return array(
            'subscription_id' => $id,
            'type' => $subscriptionService->getOneSubscriptionById($subscriptionService->getOneById($id)->getSubscription()->getId())->getType(),
            'subscription_language' => $subscriptionService->getOneById($id)->getPublication()->getLanguage()->getId(),
            'form' => $form->createView(),
            'issues' => $subscriptionService->getIssues($id),
            'sections' => $subscriptionService->getSections($id),
            'articles' => $subscriptionService->getArticles($id),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/get-subscription-details", options={"expose"=true})
     */
    public function getSubscriptionDetailsAjaxAction(Request $request)
    {
        return new Response(json_encode($this->get('subscription.service')->getSubscriptionDetails($request->get('subscriptionId'))));
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/getdata/{type}", options={"expose"=true})
     */
    public function getdata(Request $request, $type) {
        $subscriptionService = $this->get('subscription.service');
        $resultEntity = array();
        $resultSubscription = array();
        $resultArray = array();
        
        switch ($type) {
            case 'issue':
                $subscriptionEntity = $subscriptionService
                    ->getIssuesByLanguageAndId($request->get('languageId'), $request->get('subscriptionId'));
                $issues = $subscriptionService->getIssuesByLanguageId($request->get('languageId'));
                foreach ($issues as $issue) {
                    $resultEntity[$issue->getNumber()] = $issue->getName();
                }
                foreach ($subscriptionEntity as $issue) {
                    $resultSubscription[$issue->getIssueNumber()] = $issue->getName();
                }
                break;
            case 'section':
                $sectionsEntity = $subscriptionService
                    ->getSectionsByLanguageAndId($request->get('languageId'), $request->get('subscriptionId'));
                $sections = $subscriptionService->getSectionsByLanguageId($request->get('languageId'));
                foreach ($sections as $section) {
                    $resultEntity[$issue->getNumber()] = $issue->getName();
                }
                foreach ($sectionsEntity as $section) {
                    $resultSubscription[$section->getSectionNumber()] = $section->getName();
                }
                break;
            case 'article':
                $articlesEntity = $subscriptionService
                    ->getArticlesByLanguageAndId($request->get('languageId'), $request->get('subscriptionId'));
                $articles = $subscriptionService->getArticlesByLanguageId($request->get('languageId'));
                foreach ($articles as $article) {
                    $resultEntity[$article->getNumber()] = $article->getName();
                }
                foreach ($articlesEntity as $article) {
                    $resultSubscription[$article->getArticleNumber()] = $article->getName();
                }
                break;
        }
        
        $array = array_unique(array_diff($resultEntity, $resultSubscription));
        foreach ($array as $key => $value) {
            $resultArray[] = array(
                'id' => $key, 
                'name' => $value
            );
        }
        
        return new Response(json_encode($resultArray));
    }

    /**
     * Finds proper Entity object|Array Collection by given Type
     *
     * @param Doctrine\ORM\EntityManager $em
     * @param string                     $type Subscription type
     * @param string                     $id   Subscription id
     *
     * @return Entity object|Array Collection
     */
    private function findByType($em, $type, $id) {

        if ($type === 'section') {
            $subscription = $em->getRepository('Newscoop\Subscription\Section')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        if ($type === 'issue') {
            $subscription = $em->getRepository('Newscoop\Subscription\Issue')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        if ($type === 'article') {
            $subscription = $em->getRepository('Newscoop\Subscription\Article')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        if ($type === 'edit-all-issues') {
            $subscription = $em->getRepository('Newscoop\Subscription\Issue')
                ->findBy(array(
                    'subscription' => $id,
                ));
        }

        if ($type === 'edit-all-sections') {
            $subscription = $em->getRepository('Newscoop\Subscription\Section')
                ->findBy(array(
                    'subscription' => $id,
                ));
        }

        if ($type === 'edit-all-articles') {
            $subscription = $em->getRepository('Newscoop\Subscription\Article')
                ->findBy(array(
                    'subscription' => $id,
                ));
        }

        return $subscription;
    }
}