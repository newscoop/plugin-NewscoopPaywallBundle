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
        $templatesService = $this->get('newscoop.templates.service');
        $currencyContext->setCurrency($currencyProvider->getDefaultCurrency()->getCode());
        $items = $request->request->get('batchorder', array());

        if (empty($items)) {
            return new Response($templatesService->fetchTemplate(
                '_paywall/error.tpl',
                array('msg' => $translator->trans('paywall.error.noitems'))
            ), 200, array('Content-Type' => 'text/html'));
        }

        $request->getSession()->set('paywall_purchase', $items);
        $request->getSession()->set('paywall_referer', $request->headers->get('referer'));
        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $response = $purchaseService->startPurchase($items);
        if ($response && $response->isRedirect()) {
            $response->redirect();
        }

        return $this->refererRedirect($request);
    }

    /**
     * @Route("/paywall/subscriptions/order-batch", name="paywall_subscribe_order_batch", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function batchOrderAction(Request $request)
    {
        $items = $request->request->get('batchorder', array());
        $response = new JsonResponse();
        if (empty($items)) {
            $response->setStatusCode(404);

            return $response;
        }

        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $result = $purchaseService->startPurchase($items, true);
        if ($result && $request->isXmlHttpRequest()) {
            $response->headers->set('X-Location', $result);
            $response->setStatusCode(302);
        } else {
            $response->setStatusCode(502);
        }

        return $response;
    }

    /**
     * @Route("/paywall/success/", name="paywall_plugin_purchase_return", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function returnAction(Request $request)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $items = $request->getSession()->get('paywall_purchase', array());
        $purchaseService = $this->get('newscoop_paywall.services.purchase');
        $response = $purchaseService->finishPurchase($items);
        $request->getSession()->remove('paywall_purchase');

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('message' => $response->getMessage()));
        }

        if (!$response->isSuccessful() && !$response->isRedirect()) {
            return new Response($templatesService->fetchTemplate(
                '_paywall/error.tpl',
                array('msg' => $response->getMessage())
            ), 200, array('Content-Type' => 'text/html'));
        }

        return $this->refererRedirect($request);
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

    private function refererRedirect(Request $request)
    {
        $referer = $request->getSession()->get('paywall_referer', '/');

        return new RedirectResponse($referer);
    }
}
