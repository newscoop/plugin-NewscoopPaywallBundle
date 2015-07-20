<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Entity\Subscription;
use Newscoop\PaywallBundle\Entity\SubscriptionSpecification;
use Newscoop\PaywallBundle\Entity\Duration;
use Newscoop\PaywallBundle\Form\Type\DurationType;

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
            $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
                ->findActiveOneBy($id);

            if (!$subscription) {
                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            }

            $specification = $em->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
                ->findOneBy(array(
                    'subscription' => $subscription,
                ));
        } else {
            $subscription = new Subscription();
            $specification = new SubscriptionSpecification();
        }

        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $defaultCurrency = $currencyProvider->getDefaultCurrency();
        if ($defaultCurrency) {
            $defaultCurrency = $defaultCurrency->getCode();
        }
        $form = $this->createForm('subscriptionconf', $subscription);
        $subscription->setCurrency($defaultCurrency);
        $formSpecification = $this->createForm('specificationForm', $specification);
        $durationForm = $this->createForm(new DurationType());
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if (!$id) {
                    $em->persist($subscription);
                }
                $em->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(array('status' => true));
                }

                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            } else {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(array(
                        'status' => false,
                        'errors' => $this->getErrorMessages($form),
                    ));
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'formSpecification' => $formSpecification->createView(),
            'subscription_id' => $subscription->getId(),
            'ranges' => $subscription->getRanges()->toArray(),
            'formDuration' => $durationForm->createView(),
            'defaultCurrency' => $defaultCurrency,
        );
    }

    /**
     * @Route("/admin/paywall_plugin/duration/update/{id}", name="newscoop_paywall_admin_duration")
     *
     * @Method("POST")
     */
    public function durationAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $duration = new Duration();
        $form = $this->createForm(new DurationType(), $duration);
        $form->handleRequest($request);
        $errors = array();
        try {
            if ($form->isValid()) {
                if (is_null($id)) {
                    $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
                        ->findOneBy(array(
                            'name' => $request->get('subscriptionName'),
                            'is_active' => true,
                        ));

                    $id = $subscription->getId();
                }

                $durationEntity = $em->getRepository('Newscoop\PaywallBundle\Entity\Duration')
                    ->findOneBy(array(
                        'value' => $duration->getValue(),
                        'subscription' => $id,
                    ));

                if (!$durationEntity) {
                    $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
                        ->findOneBy(array(
                            'id' => $id,
                            'is_active' => true,
                        ));

                    $duration->setSubscription($subscription);
                    $em->persist($duration);
                    $em->flush();

                    $discount = array();
                    if ($duration->getDiscount()) {
                        $discount = array(
                            'name' => $duration->getDiscount()->getName(),
                            'value' => $duration->getDiscount()->getValue(),
                        );
                    }

                    return new JsonResponse(array(
                        'status' => true,
                        'duration' => array(
                            'id' => $duration->getId(),
                            'value' => $duration->getValue(),
                            'attribute' => $duration->getAttribute(),
                            'discount' => $discount,
                        ),
                    ));
                }
            } else {
                $errors = $this->getErrorMessages($form);
            }
        } catch (\Exception $e) {
            //return false status
        }

        return new JsonResponse(array(
            'status' => false,
            'errors' => $errors,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/duration/remove/{id}", name="newscoop_paywall_admin_duration_remove")
     *
     * @Method("POST")
     */
    public function durationRemoveAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $duration = $em->getRepository('Newscoop\PaywallBundle\Entity\Duration')
            ->findOneById($id);

        try {
            if ($duration) {
                $em->remove($duration);
                $em->flush();

                return new JsonResponse(array(
                    'status' => true,
                ));
            }

            return new JsonResponse(array(
                'status' => false,
            ));
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'status' => false,
                'message' => 'Cannot remove this period because its assigned for some user subscriptions',
            ));
        }
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
            $specification = $em->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
                ->findOneBy(array(
                    'subscription' => $id,
                ));
            if (!$specification) {
                $specification = new SubscriptionSpecification();
                $create = true;
            }
        } else {
            $specification = new SubscriptionSpecification();
            $create = true;
        }

        $formSpecification = $this->createForm('specificationForm', $specification);
        if ($request->isMethod('POST')) {
            $formSpecification->bind($request);
            if ($formSpecification->isValid()) {
                $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
                    ->findOneBy(array(
                        'name' => strtolower($request->request->get('subscriptionTitle')),
                        'is_active' => true,
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
            $entity = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
                ->findOneBy(array(
                    'name' => strtolower($request->request->get('subscriptionName')),
                    'is_active' => true,
                ));

            if (!$entity) {
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

    /**
     * Gets form errors.
     *
     * @param \Symfony\Component\Form\Form $form
     *
     * @return array
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
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

    /**
     * Gets all publications.
     *
     * @param Doctrine\ORM\EntityManager $em
     *
     * @return array
     */
    private function getPublication($em)
    {
        $publications = $em->getRepository('Newscoop\Entity\Publication')
            ->createQueryBuilder('p')
            ->select('p.id', 'p.name', 'l.code')
            ->leftJoin('p.language', 'l')
            ->getQuery()
            ->getArrayResult();

        return $publications;
    }

    /**
     * Gets all issues for given publication Id.
     *
     * @param Doctrine\ORM\EntityManager               $em
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getIssue($request, $em)
    {
        $issues = $em->getRepository('Newscoop\Entity\Issue')
            ->createQueryBuilder('i')
            ->select('i.number as id', 'i.name', 'l.code')
            ->leftJoin('i.language', 'l')
            ->where('i.publication = ?1')
            ->setParameter(1, $request->get('publicationId'))
            ->getQuery()
            ->getArrayResult();

        return $issues;
    }

    /**
     * Gets all sections for given publication and issue Id.
     *
     * @param Doctrine\ORM\EntityManager               $em
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getSection($request, $em)
    {
        $sections = $em->getRepository('Newscoop\Entity\Section')
            ->createQueryBuilder('s')
            ->select('s.id', 's.name', 'l.code')
            ->innerJoin('s.issue', 'i', 'WITH', 'i.number = ?2')
            ->leftJoin('s.language', 'l')
            ->where('s.publication = ?1')
            ->setParameter(1, $request->get('publicationId'))
            ->setParameter(2, $request->get('issueId'))
            ->getQuery()
            ->getArrayResult();

        return $sections;
    }

    /**
     * Gets all articles for given publication, issue, section Id.
     *
     * @param Doctrine\ORM\EntityManager               $em
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getArticle($request, $em)
    {
        $articles = $em->getRepository('Newscoop\Entity\Article')
            ->createQueryBuilder('s')
            ->select('s.number as id', 's.name', 'l.code')
            ->leftJoin('s.issue', 'i')
            ->leftJoin('s.publication', 'p')
            ->leftJoin('s.section', 'ss')
            ->leftJoin('s.language', 'l')
            ->where('s.publication = :publicationId AND ss.id = :sectionId AND i.number = :issueId')
            ->setParameters(array(
                'publicationId' => $request->get('publicationId'),
                'issueId' => $request->get('issueId'),
                'sectionId' => $request->get('sectionId'),
            ))
            ->getQuery()
            ->getArrayResult();

        return $articles;
    }

    /**
     * Gets all publications, issues, sections, articles.
     *
     * @param Doctrine\ORM\EntityManager               $em
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getAll($request, $em)
    {
        $resultArray = array(
            'Publications' => $this->getPublication($em),
            'Issues' => $this->getIssue($request, $em),
            'Sections' => $this->getSection($request, $em),
            'Articles' => $this->getArticle($request, $em),
        );

        return $resultArray;
    }
}
