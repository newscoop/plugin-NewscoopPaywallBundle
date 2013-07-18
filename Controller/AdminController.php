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
           $name = strtolower($request->request->get('subscriptionName'));
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
               ->findOneBy(array('name' => $name));

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
        if ($request->isMethod('POST')) {
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('Newscoop\Entity\Publication')
               ->findAll();
           $publications = array();
           foreach ($entity as $publication) {
               $publications[] = array($publication->getId() => $publication->getName());
           }
           
           return new Response(json_encode($publications));
       }
   }

   /**
     * @Route("/admin/paywall_plugin/getissues")
     */
    public function getIssuesAction(Request $request)
    {
        if ($request->isMethod('POST')) {
           $publication_id = $request->request->get('publicationId');
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('Newscoop\Entity\Issue')
               ->findBy(array('publication' => $publication_id));
           $issues = array();
           foreach ($entity as $issue) {
               $issues[] = array($issue->getId() => $issue->getName());
           }
           
           return new Response(json_encode($issues));
       }
   }

   /**
     * @Route("/admin/paywall_plugin/getsections")
     */
    public function getSectionsAction(Request $request)
    {
        if ($request->isMethod('POST')) {
           $publication_id = $request->request->get('publicationId');
           $issue_id = $request->request->get('issueId');
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('Newscoop\Entity\Section')
               ->findBy(array(
                 'publication' => $publication_id, 
                 'issue' => $issue_id
                ));
           $sections = array();
           foreach ($entity as $section) {
               $sections[] = array($section->getId() => $section->getName());
           }
           
           return new Response(json_encode($sections));
       }
   }

   /**
     * @Route("/admin/paywall_plugin/addspecification")
     */
    public function addSpecificationAction(Request $request)
    {
        $specification = new Subscription_specification();
        if ($request->isMethod('POST')) {
           $name = strtolower($request->request->get('subscriptionName'));
           $publication_id = $request->request->get('publicationId');
           $issue_id = $request->request->get('publicationId');
           $section_id = $request->request->get('sectionId');
           $em = $this->getDoctrine()->getManager();
           $entity = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
               ->findOneBy(array(
                  'name' => $name, 
                  'is_active' => true
                ));
           $specification->setSubscription($entity);
           $specification->setPublication($publication_id);
           $specification->setIssue($issue_id);
           $specification->setSection($section_id);
           //$specification->setArticle($publication_id);
           $em->persist($specification);
           $em->flush();

           return new Response(json_encode(array('status' => true)));
       }
   }
}