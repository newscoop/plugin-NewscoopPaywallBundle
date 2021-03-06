<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Symfony\Component\Routing\RouterInterface;
use Newscoop\PaywallBundle\Services\PaymentMethodInterface;
use Newscoop\PaywallBundle\Provider\MethodProviderInterface;
use Omnipay\Omnipay;

/**
 * Adapter Factory.
 */
class AdapterFactory
{
    const OFFLINE = 'offline';

    /**
     * Get current adapter.
     *
     * @param MethodProviderInterface $paymentMethodProvider
     * @param RouterInterface         $router
     * @param array                   $config
     * @param PaymentMethodInterface  $paymentMethodContext
     *
     * @return Omnipay
     */
    public function getAdapter(
        MethodProviderInterface $paymentMethodProvider,
        RouterInterface $router,
        PaymentMethodInterface $paymentMethodContext,
        array $config
    ) {
        $gateway = null;
        $enabledAdapter = $paymentMethodProvider->getActiveMethod();

        $gatewayName = $enabledAdapter->getValue();
        if ($paymentMethodContext->getMethod() === static::OFFLINE) {
            $gatewayName = $paymentMethodContext->getMethod();
        }

        $paymentMethodContext->setMethod($gatewayName);
        if ($paymentMethodContext->getMethod() !== static::OFFLINE) {
            $gateway = $this->initializeGateway($config, $paymentMethodContext->getMethod());
        }

        return new GatewayAdapter($router, $gateway);
    }

    private function initializeGateway(array $config, $name)
    {
        if (!isset($config['gateways'][$name])) {
            throw new \InvalidArgumentException(
                '"'.$name.'" gateway is not configured! Make sure it is '.
                'installed via Composer and that you added it to custom_parameters.yml'
            );
        }

        $gateway = Omnipay::create($name);
        $gateway->initialize(array_merge(
            $config['gateways'][$name],
            array(
                'brandName' => $config['brandName'],
            )
        ));

        return $gateway;
    }
}
