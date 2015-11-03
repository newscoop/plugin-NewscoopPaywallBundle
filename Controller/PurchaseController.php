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
use Newscoop\PaywallBundle\Entity\Payment;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\OrderInterface;

/**
 * It handles actions after payment redirects.
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
        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $currencyContext = $this->get('newscoop_paywall.currency_context');
        $currencyContext->setCurrency($currencyProvider->getDefaultCurrency()->getCode());
        $items = $request->request->get('batchorder', array());

        if (empty($items)) {
            return new Response($templatesService->fetchTemplate(
                '_paywall/error.tpl',
                array('msg' => 'no items')
            ), 200, array('Content-Type' => 'text/html'));
        }

        $request->getSession()->set('paywall_purchase', $items);

        $orderService = $this->get('newscoop_paywall.services.order');
        $order = $orderService->processAndCalculateOrderItems($items);

        if (!$order->getItems()->isEmpty()) {
            $adapter = $this->get('newscoop.paywall.adapter');

            $response = $adapter->purchase($order);

            if ($response->isSuccessful()) {
                $this->completePurchase($order);
            } elseif ($response->isRedirect()) {
                // redirect to offsite payment gateway
                  $response->redirect();
            } else {
                // payment failed: display message to customer
                return new Response($templatesService->fetchTemplate(
                    '_paywall/error.tpl',
                    array('msg' => $response->getMessage())
                ), 200, array('Content-Type' => 'text/html'));
            }
        }

        return new Response($templatesService->fetchTemplate(
            '_paywall/success.tpl'
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
        $templatesService = $this->get('newscoop.templates.service');

        $orderService = $this->get('newscoop_paywall.services.order');
        $order = $orderService->processAndCalculateOrderItems($items);

        $adapter = $this->container->getService('newscoop.paywall.adapter');

        $response = $adapter->completePurchase($order);

        if ($response->isSuccessful()) {
            $this->completePurchase($order);
        } elseif ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
        } else {
            // payment failed: display message to customer
            return new Response($templatesService->fetchTemplate(
                '_paywall/error.tpl',
                array('msg' => $response->getMessage())
            ), 200, array('Content-Type' => 'text/html'));
        }

        return new Response($templatesService->fetchTemplate(
            '_paywall/success.tpl'
        ), 200, array('Content-Type' => 'text/html'));
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

    private function completePurchase(OrderInterface $order)
    {
        $entityManager = $this->get('em');
        $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $order->getItems()->toArray());
        $this->get('newscoop_paywall.services.payment')->createPayment($order);

        $entityManager->persist($order);
        $entityManager->flush();
    }
}
