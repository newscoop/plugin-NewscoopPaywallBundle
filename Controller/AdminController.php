<?php
/**
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @package Newscoop\PaywallBundle
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Form\SubscriptionConfType;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Entity\Subscription_specification;

class AdminController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin")
     * @Template()
     */
    public function adminAction(Request $request)
    {
    	/*$this->container->get('dispatcher')->dispatch('plugin.install', new \Newscoop\EventDispatcher\Events\GenericEvent($this, array(
                'Paywall Plugin' => ''
                )));*/
         $subscription = new Subscriptions();
         $form = $this->createForm('subscriptionconf', $subscription);
         if ($request->isMethod('POST')) {
            $form->bind($request);
            if($form->isValid()) {
                $data = $request->request->get($form->getName());
                $subscription->setName($data['name']);
                $subscription->setType($data['type']);
                $subscription->setRange($data['range']);
                $subscription->setPrice($data['price']);
                $subscription->setCurrency($data['currency']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($subscription);
                $em->flush();
                
                return $this->redirect($this->generateUrl('newscoop_paywall_admin_admin'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    } 

    /**
     * @Route("/admin/paywall_plugin/check")
     */
    public function checkAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array(
                    'name' => strtolower($request->request->get('subscriptionName'))
                ));

            if(!$entity) {
                return new Response(json_encode(array('status' => true)));
            }

            return new Response(json_encode(array('status' => false)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/getpublications")
     */
    public function getPublicationsAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            //TODO: chnage that to smaller query - get only id and name with query builder.
            $publications = $em->getRepository('Newscoop\Entity\Publication')
                ->findAll();

            $publicationsArray = array();
            foreach ($publications as $publication) {
                $publicationsArray[] = array(
                    'id' => $publication->getId(),
                    'text' => $publication->getName()
                );
            }
           
            return new Response(json_encode($publicationsArray));
   }

    /**
     * @Route("/admin/paywall_plugin/getissues")
     */
    public function getIssuesAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            //TODO: chnage that to smaller query - get only id and name with query builder.
            $issues = $em->getRepository('Newscoop\Entity\Issue')
                ->findBy(array(
                    'publication' => $request->get('publicationId')
                ));

            $issuesArray = array();
            foreach ($issues as $issue) {
                $issuesArray[] = array(
                    'id' => $issue->getId(), 
                    'text' => $issue->getName()
                );
            }
           
            return new Response(json_encode($issuesArray));
    }

    /**
     * @Route("/admin/paywall_plugin/getsections")
     */
    public function getSectionsAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            //TODO: chnage that to smaller query - get only id and name with query builder.
            $sections = $em->getRepository('Newscoop\Entity\Section')
                ->findBy(array(
                    'publication' => $request->get('publicationId'), 
                    'issue' => $request->get('issueId')
                ));
            $sectionsArray = array();
            foreach ($sections as $section) {
                $sectionsArray[] = array(
                    'id' => $section->getId(), 
                    'text' => $section->getName()
                );
            }
           
            return new Response(json_encode($sectionsArray));
    }

    /**
     * @Route("/admin/paywall_plugin/getarticles")
     */
    public function getArticlesAction(Request $request)
    {
            
            $em = $this->getDoctrine()->getManager();
            //TODO: chnage that to smaller query - get only id and name with query builder.
            $articles = $em->getRepository('Newscoop\Entity\Article')
                ->findBy(array(
                    'publication' => $request->get('publicationId'), 
                    'issue' => $request->get('issueId'),
                    'section' => $request->get('sectionId')
                ));
            $articlesArray = array();
            foreach ($articles as $article) {
                $articlesArray[] = array(
                    'id' => $article->getId(), 
                    'text' => $article->getName()
                );
            }
           
            return new Response(json_encode($articlesArray));
    }

    /**
     * @Route("/admin/paywall_plugin/addspecification")
     */
    public function addSpecificationAction(Request $request)
    {
        $specification = new Subscription_specification();
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array(
                    'name' => strtolower($request->request->get('subscriptionName')), 
                    'is_active' => true
                ));
            //TODO: we need form and validation here.
            $specification->setSubscription($subscription);
            $specification->setPublication($request->get('publicationId'));
            $specification->setIssue($request->get('issueId'));
            $specification->setSection($request->get('sectionId'));
            $specification->setArticle($request->get('articleId'));
            $em->persist($specification);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }
    }
}