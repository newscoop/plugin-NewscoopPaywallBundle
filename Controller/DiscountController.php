<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

    private function getDiscountRepository()
    {
        $em = $this->get('em');

        return $em->getRepository('Newscoop\PaywallBundle\Entity\Discount');
    }

    /**
     * @Route("/admin/paywall_plugin/create/", options={"expose"=true}, name="paywall_plugin_discount_create")
     */
    public function createAction(Request $request)
    {
        $discount = new Discount();
        $form = $this->createForm(new DiscountType(), $discount);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
        }

        return $this->render('NewscoopPaywallBundle:Discount:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/delete/{id}", options={"expose"=true}, name="paywall_plugin_discount_delete")
     */
    public function deleteAction(Request $request, Discount $discount)
    {
        return array();
    }

    /**
     * @Route("/admin/paywall_plugin/edit/{id}", options={"expose"=true}, name="paywall_plugin_discount_edit")
     */
    public function editAction(Request $request, Discount $discount)
    {
        return array();
    }

    /**
     * @Route("/admin/paywall_plugin/show/{id}", options={"expose"=true}, name="paywall_plugin_discount_show")
     */
    public function showAction(Request $request, Discount $discount)
    {
        return array();
    }
}
