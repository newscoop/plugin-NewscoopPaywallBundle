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

                if ($request->isXmlHttpRequest()) {
                    return array('status' => true);
                }
                
                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            } else {
                if ($request->isXmlHttpRequest()) {
                    return array(
                        'status' => false,
                        'errors' => json_encode($this->getErrorMessages($form))
                    );
                }
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/admin/paywall_plugin/step2")
     */
    public function step2Action(Request $request)
    {
        $specification = new Subscription_specification();
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array(
                    'name' => strtolower($request->request->get('subscriptionTitle')), 
                    'is_active' => true
                ));
            //TODO: we need form and validation here.
            $specification->setSubscription($subscription);
            $specification->setPublication($request->get('publicationId'));
            $specification->setIssue($request->get('issueId'));
            $specification->setSection($request->get('sectionId'));
            //TODO: add articleNumber and ArticleLanguage - we don't have articleId
            $specification->setArticle($request->get('articleNumber'));
            $em->persist($specification);
            $em->flush();
            
            return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
        }
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
                    'name' => strtolower($request->request->get('subscriptionName')),
                    'is_active' => true
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
                ->createQueryBuilder('p')
                ->select('p.id', 'p.name')
                ->getQuery()
                ->getArrayResult();
           
            return new Response(json_encode($publications));
   }

    /**
     * @Route("/admin/paywall_plugin/getissues")
     */
    public function getIssuesAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            $issues = $em->getRepository('Newscoop\Entity\Issue')
                ->createQueryBuilder('p')
                ->select('p.id', 'p.name')
                ->getQuery()
                ->getArrayResult();
           
            return new Response(json_encode($issues));
    }

    /**
     * @Route("/admin/paywall_plugin/getsections")
     */
    public function getSectionsAction(Request $request)
    {
            $em = $this->getDoctrine()->getManager();
            $sections = $em->getRepository('Newscoop\Entity\Section')
                ->createQueryBuilder('p')
                ->select('p.id', 'p.name')
                ->getQuery()
                ->getArrayResult();
           
            return new Response(json_encode($sections));
    }

    /**
     * @Route("/admin/paywall_plugin/getarticles")
     */
    public function getArticlesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $section = $em->getRepository('Newscoop\Entity\Section')
            ->findOneBy(array(
                'id' => $request->get('sectionId'),
                'publication' => $request->get('publicationId'), 
                'issue' => $request->get('issueId')
            ));

        $articles = $em->getRepository('Newscoop\Entity\Article')
            ->getArticlesForSection($request->get('publicationId'), $section->getNumber())
            ->getResult();

        $articlesArray = array();
        foreach ($articles as $article) {
            $articlesArray[] = array(
                'id' => $article->getNumber(), 
                'text' => $article->getName()
            );
        }
       
        return new Response(json_encode($articlesArray));
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {      
        $errors = array();
        if (count($form) > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();   
        }

        return $errors;
    }
}