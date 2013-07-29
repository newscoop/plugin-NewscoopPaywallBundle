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
use Newscoop\PaywallBundle\Form\SubscriptionConfType;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Entity\Subscription_specification;
use Doctrine\ORM\Query\Expr\Join;

class AdminController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin")
     * @Route("/admin/paywall_plugin/update/{id}", name="newscoop_paywall_admin_update", options={"expose"=true})
     * @Template()
     */
    public function adminAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($id) {
            $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array(
                    'id' => $id,
                    'is_active' => true
                ));

            if (!$subscription) {
                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            }

            $specification = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription_specification')
                ->findOneBy(array(
                    'subscription' => $subscription
                ));
        } else {
            $subscription = new Subscriptions();
            $specification = new Subscription_specification();
        }

        $form = $this->createForm('subscriptionconf', $subscription);
        $formSpecification = $this->createForm('specificationForm', $specification);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if (!$id) {
                    $em->persist($subscription);
                }
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
            'form' => $form->createView(),
            'formSpecification' => $formSpecification->createView(),
            'subscription_id' => $subscription->getId()
        );
    }

    /**
     * @Route("/admin/paywall_plugin/step2")
     * @Route("/admin/paywall_plugin/step2/update/{id}", name="newscoop_paywall_admin_step2")
     */
    public function step2Action(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $create = false;
        if ($id) {
            $specification = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription_specification')
                ->findOneBy(array(
                    'subscription' => $id
                ));
            if (!$specification) {
                $specification = new Subscription_specification();
                $create = true;
            }
        } else {
            $specification = new Subscription_specification();
            $create = true;
        }
        
        $formSpecification = $this->createForm('specificationForm', $specification);
        if ($request->isMethod('POST')) {
            $formSpecification->bind($request);
            if($formSpecification->isValid()) {
                $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                    ->findOneBy(array(
                        'name' => strtolower($request->request->get('subscriptionTitle')), 
                        'is_active' => true
                    ));
                $specification->setSubscription($subscription);
                if (!$id || $create) {
                    $em->persist($specification);
                }
                $em->flush();
            
                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            }
        }
    } 

    /**
     * @Route("/admin/paywall_plugin/check", options={"expose"=true})
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
     * @Route("/admin/paywall_plugin/getall", options={"expose"=true})
     */
    public function getAllAction(Request $request)
    {
        return new Response(json_encode($this->getAll($request, $this->getDoctrine()->getManager()))); 
    }

    /**
     * @Route("/admin/paywall_plugin/getpublications", options={"expose"=true})
     */
    public function getPublicationsAction(Request $request)
    {       
        return new Response(json_encode($this->getPublication($this->getDoctrine()->getManager()))); 
    }

    /**
     * @Route("/admin/paywall_plugin/getissues", options={"expose"=true})
     */
    public function getIssuesAction(Request $request)
    {
        return new Response(json_encode($this->getIssue($request, $this->getDoctrine()->getManager()))); 
    }

    /**
     * @Route("/admin/paywall_plugin/getsections", options={"expose"=true})
     */
    public function getSectionsAction(Request $request)
    {
        return new Response(json_encode($this->getSection($request, $this->getDoctrine()->getManager()))); 
    }

    /**
     * @Route("/admin/paywall_plugin/getarticles", options={"expose"=true})
     */
    public function getArticlesAction(Request $request)
    {
        return new Response(json_encode($this->getArticle($request, $this->getDoctrine()->getManager()))); 
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

    private function getPublication($em) {

        $publications = $em->getRepository('Newscoop\Entity\Publication')
            ->createQueryBuilder('p')
            ->select('p.id', 'p.name')
            ->getQuery()
            ->getArrayResult();

        return $publications;
    }

    private function getIssue($request, $em) {

        $issues = $em->getRepository('Newscoop\Entity\Issue')
            ->createQueryBuilder('i')
            ->select('i.number as id', 'i.name')
            ->where('i.publication = ?1')
            ->setParameter(1, $request->get('publicationId'))
            ->getQuery()
            ->getArrayResult();

        return $issues;
    }

    private function getSection($request, $em) {
        
        $sections = $em->getRepository('Newscoop\Entity\Section')
            ->createQueryBuilder('s')
            ->select('s.id', 's.name')
            ->innerJoin('s.issue', 'i', 'WITH', 'i.number = ?2')
            ->where('s.publication = ?1')
            ->setParameter(1, $request->get('publicationId'))
            ->setParameter(2, $request->get('issueId'))
            ->getQuery()
            ->getArrayResult();

        return $sections;
    }

    private function getArticle($request, $em) {
        
        $number = $em->getRepository('Newscoop\Entity\Section')
            ->createQueryBuilder('s')
            ->select('s.number')
            ->innerJoin('s.issue', 'i', 'WITH', 'i.number = :issueId')
            ->where('s.publication = :publicationId AND s.id = :sectionId')
            ->setParameters(array(
                'publicationId' => $request->get('publicationId'), 
                'issueId' => $request->get('issueId'),
                'sectionId' => $request->get('sectionId')
            ))
            ->getQuery()
            ->getOneOrNullResult();

        $articles = $em->getRepository('Newscoop\Entity\Article')
            ->getArticlesForSection($request->get('publicationId'), reset($number))
            ->getResult();

        $articlesArray = array();
        foreach ($articles as $article) {
            $articlesArray[] = array(
                'id' => $article->getNumber(), 
                'text' => $article->getName()
            );
        }

        return $articlesArray;
    }

    private function getAll($request, $em) {

        $resultArray = array(
            'Publications' => $this->getPublication($em), 
            'Issues' => $this->getIssue($request, $em), 
            'Sections' => $this->getSection($request, $em), 
            'Articles' => $this->getArticle($request, $em)
        );

        return $resultArray;
    }
}