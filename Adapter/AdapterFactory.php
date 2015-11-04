<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Routing\RouterInterface;
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
     * @param ObjectRepository $gatewayRepository
     * @param RouterInterface  $router
     * @param array            $config
     *
     * @return Omnipay
     */
    public function getAdapter(ObjectRepository $gatewayRepository, RouterInterface $router, array $config)
    {
        $gateway = null;
        $enabledAdapter = $gatewayRepository->findOneByIsActive(true);
        if ($enabledAdapter->getValue() !== static::OFFLINE) {
            $gateway = $this->initializeGateway($config, $enabledAdapter->getValue());
        }

        return new GatewayAdapter($router, $gateway);
    }

    private function initializeGateway(array $config, $name)
    {
        if (!isset($config[$name])) {
            throw new \InvalidArgumentException(
                '"'.$name.'" gateway is not configured! Make sure it is '.
                'installed via Composer and that you added it to custom_parameters.yml'
            );
        }

        $gateway = Omnipay::create($name);
        $gateway->initialize($config[$name]);

        return $gateway;
    }
}
