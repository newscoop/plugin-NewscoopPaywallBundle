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
use Newscoop\PaywallBundle\Entity\Payment;

class PaymentController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/payments/", name="paywall_plugin_payment_index", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $query = $this->getRepository()->findAllAvailable();
        $paginator = $this->get('knp_paginator');
        $payments = $paginator->paginate(
            $query,
            $request->query->getInt('knp_page', 1),
            20
        );

        $payments->setTemplate('NewscoopNewscoopBundle:Pagination:pagination_bootstrap3.html.twig');

        return $this->render('NewscoopPaywallBundle:Payment:index.html.twig', array(
            'payments' => $payments,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/payments/create/", name="paywall_plugin_payment_create", options={"expose"=true})
     */
    public function createAction(Request $request)
    {
    }

    /**
     * @Route("/admin/paywall_plugin/payments/edit/{id}", name="paywall_plugin_payment_edit", options={"expose"=true})
     */
    public function editAction(Request $request, Payment $payment)
    {
    }

    /**
     * @Route("/admin/paywall_plugin/payments/delete/{id}", name="paywall_plugin_payment_delete", options={"expose"=true})
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Payment $payment)
    {
    }

    private function getRepository()
    {
        $repository = $this->get('em')->getRepository('Newscoop\PaywallBundle\Entity\Payment');

        return $repository;
    }
}
