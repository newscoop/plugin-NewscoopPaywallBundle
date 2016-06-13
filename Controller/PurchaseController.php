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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * It handles purchase actions.
 */
class PurchaseController extends BaseController
{
    /**
     * @Route("/paywall/purchase/", name="paywall_plugin_purchase_purchase", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function purchaseAction(Request $request)
    {
        $translator = $this->get('translator');
        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $currencyContext = $this->get('newscoop_paywall.currency_context');
        $currencyContext->setCurrency($currencyProvider->getDefaultCurrency()->getCode());
        $items = $request->getSession()->get('paywall_purchase', array());

        if (empty($items)) {
            return $this->loadErrorTemplate($translator->trans('paywall.error.noitems'));
        }

        $method = $request->request->get('paymentMethod');
        $paymentMethodContext = $this->get('newscoop_paywall.payment_method_context');
        $paymentMethodContext->setMethod($method);
        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $response = $purchaseService->startPurchase($items);
        if ($response && $response->isRedirect()) {
            $response->redirect();
        }

        if ($response && !$response->isSuccessful()) {
            return $this->loadErrorTemplate($response->getMessage());
        }

        return $this->redirectToThankYou();
    }

    /**
     * @Route("/paywall/subscriptions/order-batch/{currency}", name="paywall_subscribe_order_batch", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function batchOrderAction(Request $request, $currency)
    {
        $items = $request->request->get('batchorder', array());
        $response = new JsonResponse();
        if (empty($items)) {
            $response->setStatusCode(404);

            return $response;
        }

        $method = $request->request->get('paymentMethod');
        if (null !== $method) {
            $paymentMethodContext = $this->get('newscoop_paywall.payment_method_context');
            $paymentMethodContext->setMethod($method);
        }

        $request->getSession()->set('paywall_purchase', $items);

        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $result = $purchaseService->startPurchase($items, $currency);

        if (null === $result) {
            $response->setStatusCode(204);

            return $response;
        }

        $data = $result->getData();
        if (isset($data['ACK']) && 'Success' === $data['ACK']) {
            $response->headers->set('X-Location', $result->getRedirectUrl());
            $response->setStatusCode(302);
        } else {
            $response->setStatusCode(502);
        }

        return $response;
    }

    /**
     * @Route("/paywall/purchase/methods/", name="paywall_plugin_purchase_methods", options={"expose"=true})
     */
    public function methodsAction(Request $request)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $translator = $this->get('translator');

        $items = $request->query->get('batchorder', array());
        if (empty($items)) {
            return $this->loadErrorTemplate($translator->trans('paywall.error.noitems'));
        }

        $request->getSession()->set('paywall_purchase', $items);

        $order = $this->get('newscoop_paywall.services.order')->processAndCalculateOrderItems($items);

        return new Response($templatesService->fetchTemplate(
            '_paywall/payment_methods.tpl',
            array(
                'amount' => $order->getTotal(),
                'currency' => $order->getCurrency(),
            )
        ), 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/paywall/success/", name="paywall_plugin_purchase_return", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function returnAction(Request $request)
    {
        $items = $request->getSession()->get('paywall_purchase', array());
        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $response = $purchaseService->finishPurchase($items);
        $request->getSession()->remove('paywall_purchase');

        if (!$response->isSuccessful() && !$response->isRedirect()) {
            return $this->loadErrorTemplate($response->getMessage());
        }

        return $this->redirectToThankYou();
    }

    /**
     * @Route("/paywall/cancel/", name="paywall_plugin_purchase_cancel", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function cancelAction()
    {
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_paywall/cancel.tpl'
        ), 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @Route("/paywall/thank-you/", name="paywall_plugin_purchase_thank_you", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function thankYouAction()
    {
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_paywall/thankyou.tpl'
        ), 200, array('Content-Type' => 'text/html'));
    }

    private function loadErrorTemplate($message = '')
    {
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_paywall/error.tpl',
            array('msg' => $message)
        ), 200, array('Content-Type' => 'text/html'));
    }

    private function redirectToThankYou()
    {
        return new RedirectResponse($this->get('router')->generate('paywall_plugin_purchase_thank_you'));
    }
}
