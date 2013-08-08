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
use Newscoop\Subscription\SectionFacade;
use Newscoop\Entity\User\Subscriber;

class UsersSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/users-subscriptions", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array(
            'subscriptions' => $this->get('subscription.service')->getByAll(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/delete/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $this->get('subscription.service')->removeById($id);

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
     * @Route("/admin/paywall_plugin/users-subscriptions/add", options={"expose"=true})
     */
    public function addAction(Request $request)
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
                $language = $subscriptionService->getLanguageRepository()
                    ->findOneById($request->get('languageId'));

                $section = $subscriptionService->getSectionRepository()
                    ->findOneByNumber($request->request->get('sectionId'));

                $subscriptionData->addSection($section, $language);

                $subscription = $subscriptionService->update($subscription, $subscriptionData);
                $subscriptionService->save($subscription);

                return $this->redirect($this->generateUrl('newscoop_paywall_userssubscriptions_details', 
                        array(
                            'id' => $subscription->getId(), 
                        )
                ));
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
            'id' => $subscription->getId(),
            'type' => $type,
            'subscription' => $subscription->getSubscription()->getId(),
            'name' => $subscription->getName(),
            'language' => $subscription->getLanguage()->getName(),
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
            'id' => $subscription->getId(),
            'username' => $subscription->getUser()->getUsername(),
            'name' => $subscription->getUser()->getName(),
            'publication' => $subscription->getPublicationName(),
            'topay' => $subscription->getToPay(),
            'currency' => $subscription->getCurrency(),
            'type' => $subscription->getType(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/details/{id}", options={"expose"=true})
     * @Template()
     */
    public function detailsAction(Request $request, $id)
    {
        $service = $this->get('subscription.service');
        $form = $this->createForm('detailsForm');

        return array(
            'subscription_id' => $id,
            'form' => $form->createView(),
            'issues' => $service->getIssues($id),
            'sections' => $service->getSections($id),
            'articles' => $service->getArticles($id),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/getsections", options={"expose"=true})
     */
    public function getSections(Request $request) {
        $subscriptionService = $this->get('subscription.service');

        $subscriptionSections = $subscriptionService
            ->getSectionsByLanguageAndId($request->get('languageId'), $request->get('subscriptionId'));

        $entitySections = $subscriptionService->getSectionsByLanguageId($request->get('languageId'));

        $sections = array();
        $sectionsSub = array();
        foreach ($entitySections as $section) {
            $sections[$section->getNumber()] = $section->getName();
        }

        foreach ($subscriptionSections as $section) {
            $sectionsSub[$section->getSectionNumber()] = $section->getName();
        }

        $array = array_unique(array_diff($sections, $sectionsSub));
        $resultArray = array();
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
     * @param string $type                   Subscription type
     * @param string $id                     Subscription id
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