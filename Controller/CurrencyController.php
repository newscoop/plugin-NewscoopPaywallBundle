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
use Newscoop\PaywallBundle\Entity\Currency;
use Newscoop\PaywallBundle\Form\Type\CurrencyType;
use Sylius\Component\Currency\Model\CurrencyInterface;

class CurrencyController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/currencies/", name="paywall_plugin_currency_index", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $query = $this->getRepository()->findAllAvailable();
        $paginator = $this->get('knp_paginator');
        $currencies = $paginator->paginate(
            $query,
            $request->query->getInt('knp_page', 1),
            20
        );

        $currencies->setTemplate('NewscoopNewscoopBundle:Pagination:pagination_bootstrap3.html.twig');

        return $this->render('NewscoopPaywallBundle:Currency:index.html.twig', array(
            'currencies' => $currencies,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/currencies/create/", name="paywall_plugin_currency_create", options={"expose"=true})
     */
    public function createAction(Request $request)
    {
        $currency = $this->getRepository()->createNew();
        $form = $this->createForm(new CurrencyType(), $currency);
        $em = $this->get('em');
        $translator = $this->get('translator');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if (!$this->findByCode($currency)) {
                    $em->persist($currency);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.created'));
                } else {
                    $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.exists'));
                }

                return $this->redirect($this->generateUrl('paywall_plugin_currency_index'));
            }
        }

        return $this->render('NewscoopPaywallBundle:Currency:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/currencies/edit/{id}", name="paywall_plugin_currency_edit", options={"expose"=true})
     */
    public function editAction(Request $request, Currency $currency)
    {
        $form = $this->createForm(new CurrencyType(), $currency);
        $em = $this->get('em');
        $translator = $this->get('translator');
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                if (!$this->checkForExistenceBy($currency)) {
                    $currency->setUpdatedAt(new \DateTime('now'));
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.saved'));

                    return $this->redirect($this->generateUrl('paywall_plugin_currency_index'));
                }

                $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.error.exists', array(
                    '%resource%' => $currency->getName(),
                )));

                return $this->redirect($this->generateUrl('paywall_plugin_currency_edit', array(
                    'id' => $currency->getId(),
                )));
            }
        }

        return $this->render('NewscoopPaywallBundle:Currency:edit.html.twig', array(
            'form' => $form->createView(),
            'currency' => $currency,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/currencies/delete/{id}", name="paywall_plugin_currency_delete", options={"expose"=true})
     *
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Currency $currency)
    {
        $translator = $this->get('translator');
        if ($this->findByCode($currency)) {
            $em = $this->get('em');
            $em->remove($currency);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.removed'));
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.success.notexists'));
        }

        return $this->redirect($this->generateUrl('paywall_plugin_currency_index'));
    }

    private function findByCode(CurrencyInterface $currency)
    {
        if ($this->getRepository()->findOneByCode($currency->getCode())) {
            return true;
        }

        return false;
    }

    private function checkForExistenceBy(CurrencyInterface $currency)
    {
        $result = $this->getRepository()->checkIfExists($currency)
            ->getSingleScalarResult();

        if ((int) $result > 0) {
            return true;
        }

        return false;
    }

    private function getRepository()
    {
        $repository = $this->get('newscoop_paywall.currency.repository');

        return $repository;
    }
}
