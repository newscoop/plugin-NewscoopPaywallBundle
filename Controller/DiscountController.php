<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Form\Type\DiscountType;
use Newscoop\PaywallBundle\Entity\Discount;

class DiscountController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/discounts", options={"expose"=true})
     */
    public function indexAction(Request $request)
    {
        $query = $this->getDiscountRepository()->findActive();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('NewscoopPaywallBundle:Discount:index.html.twig', array(
            'pagination' => $pagination,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/discounts/create/", options={"expose"=true}, name="paywall_plugin_discount_create")
     */
    public function createAction(Request $request)
    {
        $discount = new Discount();
        $form = $this->createForm(new DiscountType(), $discount);
        $em = $this->get('em');
        $translator = $this->get('translator');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if (!$this->exists($discount)) {
                    $em->persist($discount);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.created'));
                } else {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.exists'));
                }

                return $this->redirect($this->generateUrl('newscoop_paywall_discount_index'));
            }
        }

        return $this->render('NewscoopPaywallBundle:Discount:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/discounts/delete/{id}", options={"expose"=true}, name="paywall_plugin_discount_delete")
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Discount $discount)
    {
        $translator = $this->get('translator');
        if ($this->exists($discount)) {
            $em = $this->get('em');
            $em->remove($discount);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.removed'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.notexists'));
        }

        return $this->redirect($this->generateUrl('newscoop_paywall_discount_index'));
    }

    /**
     * @Route("/admin/paywall_plugin/discounts/edit/{id}", options={"expose"=true}, name="paywall_plugin_discount_edit")
     */
    public function editAction(Request $request, Discount $discount)
    {
        $form = $this->createForm(new DiscountType(), $discount);
        $em = $this->get('em');
        $translator = $this->get('translator');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if (!$this->checkForExistenceBy($discount)) {
                    $discount->setUpdatedAt(new \DateTime('now'));
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.saved'));

                    return $this->redirect($this->generateUrl('newscoop_paywall_discount_index'));
                }

                $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.exists'));

                return $this->redirect($this->generateUrl('paywall_plugin_discount_edit', array(
                    'id' => $discount->getId(),
                )));
            }
        }

        return $this->render('NewscoopPaywallBundle:Discount:edit.html.twig', array(
            'form' => $form->createView(),
            'discountId' => $discount->getId(),
        ));
    }

    private function exists(Discount $discount)
    {
        if ($this->getDiscountRepository()->findOneByName($discount->getName())) {
            return true;
        }

        return false;
    }

    private function checkForExistenceBy(Discount $discount)
    {
        $result = $this->getDiscountRepository()->createQueryBuilder('d')
            ->select('count(d)')
            ->where('d.name = :name')
            ->andWhere('d.id <> :id')
            ->setParameter('name', $discount->getName())
            ->setParameter('id', $discount->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $result > 0) {
            return true;
        }

        return false;
    }

    private function getDiscountRepository()
    {
        $em = $this->get('em');

        return $em->getRepository('Newscoop\PaywallBundle\Entity\Discount');
    }
}
