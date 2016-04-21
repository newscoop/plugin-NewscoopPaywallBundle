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
use Newscoop\PaywallBundle\Form\Type\PaymentType;
use Newscoop\PaywallBundle\Permissions;

class PaymentController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/payments/", name="paywall_plugin_payment_index", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $this->hasPermission(Permissions::PAYMENTS_VIEW);
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
     * @Route("/admin/paywall_plugin/payments/edit/{id}", name="paywall_plugin_payment_edit", options={"expose"=true})
     */
    public function editAction(Request $request, Payment $payment)
    {
        $this->hasPermission(Permissions::PAYMENTS_MANAGE);
        $form = $this->createForm(new PaymentType(), $payment);
        $entityManager = $this->get('em');
        $translator = $this->get('translator');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $payment->setUpdatedAt(new \DateTime('now'));
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.saved'));

                return $this->redirect($this->generateUrl('paywall_plugin_payment_index'));
            }
        }

        return $this->render('NewscoopPaywallBundle:Payment:edit.html.twig', array(
            'form' => $form->createView(),
            'payment' => $payment,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/payments/delete/{id}", name="paywall_plugin_payment_delete", options={"expose"=true})
     *
     * @Method("DELETE")
     */
    public function deleteAction(Payment $payment)
    {
        $this->hasPermission(Permissions::PAYMENTS_MANAGE);
        $translator = $this->get('translator');
        if ($this->getRepository()->findOneById($payment->getId())) {
            $entityManager = $this->get('em');
            $entityManager->remove($payment);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.removed'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.notexists'));
        }

        return $this->redirect($this->generateUrl('paywall_plugin_payment_index'));
    }

    private function getRepository()
    {
        $repository = $this->get('newscoop_paywall.services.payment')->getRepository();

        return $repository;
    }
}
