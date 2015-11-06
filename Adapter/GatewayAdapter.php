<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Entity\OrderInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Omnipay adapter.
 */
class GatewayAdapter
{
    /**
     * Router.
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * Gateway.
     *
     * @var Omnipay\Common\GatewayInterface|null
     */
    protected $gateway;

    /**
     * Construct.
     *
     * @param RouterInterface                      $router
     * @param Omnipay\Common\GatewayInterface|null $gateway
     */
    public function __construct(RouterInterface $router, $gateway = null)
    {
        $this->router = $router;
        $this->gateway = $gateway;
    }

    /**
     * Purchase action.
     *
     * @param OrderInterface $order Order
     *
     * @return Omnipay\Common\Message\ResponseInterface
     */
    public function purchase(OrderInterface $order)
    {
        if ($this->gateway === null) {
            return;
        }

        return $this->gateway->purchase(array_merge(
            array(
                'amount' => $order->getTotal(),
                'currency' => $order->getCurrency(),
            ),
            $this->getCancelAndReturnUrl()
        ))->send();
    }

    /**
     * Cplete purchase action.
     *
     * @param OrderInterface $order Order
     *
     * @return Omnipay\Common\Message\ResponseInterface
     */
    public function completePurchase(OrderInterface $order)
    {
        if ($this->gateway === null) {
            return;
        }

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
}
