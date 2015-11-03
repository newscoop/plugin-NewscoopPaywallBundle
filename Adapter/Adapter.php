<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Services\PaywallService;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Omnipay\Omnipay;

/**
 * Omnipay adapter.
 */
class Adapter
{
    protected $subscriptionService;

    protected $request;

    protected $gateway;

    protected $router;

    public function __construct(PaywallService $subscriptionService, $router, array $config = array())
    {
        $this->subscriptionService = $subscriptionService;
        $this->config = $config;
        $this->router = $router;
        $this->initializeGateway();
    }

    private function initializeGateway()
    {
        if (!isset($this->config['PayPal_Express'])) {
            throw new \InvalidArgumentException('Gateway "'.'PayPal_Express'.'" is not configured!');
        }

        // TODO get name from db
        $this->gateway = Omnipay::create('PayPal_Express');
        $this->gateway->initialize($this->config['PayPal_Express']);
    }

    public function purchase(OrderInterface $order)
    {
        return $this->gateway->purchase(array_merge(
            array(
                'amount' => $order->getTotal(),
                'currency' => $order->getCurrency(),
            ),
            $this->getCancelAndReturnUrl()
        ))->send();
    }

    public function completePurchase(OrderInterface $order)
    {
        return $this->gateway->completePurchase(array_merge(
            array(
                'amount' => $order->getTotal(),
                'currency' => $order->getCurrency(),
            ),
            $this->getCancelAndReturnUrl()
        ))->send();
    }

    private function getCancelAndReturnUrl()
    {
        return array(
            'cancelUrl' => $this->router->generate('paywall_plugin_purchase_cancel', array(), true),
            'returnUrl' => $this->router->generate('paywall_plugin_purchase_return', array(), true),
        );
    }

    protected function getConfig()
    {
        return $this->config;
    }
}
