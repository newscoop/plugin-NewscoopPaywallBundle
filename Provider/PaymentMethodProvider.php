<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Provider;

use Newscoop\PaywallBundle\Entity\Repository\GatewayRepository;
use Newscoop\PaywallBundle\Adapter\AdapterFactory;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Payment Method Provider class.
 */
class PaymentMethodProvider implements MethodProviderInterface
{
    /**
     * @var ObjectRepository
     */
    protected $gatewayRepository;

    /**
     * @param ObjectRepository $gatewayRepository
     */
    public function __construct(ObjectRepository $gatewayRepository)
    {
        $this->gatewayRepository = $gatewayRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveMethod()
    {
        return $this->gatewayRepository
            ->findOneBy(array(
                'isActive' => true,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMethod()
    {
        return $this->gatewayRepository
            ->findOneBy(array(
                'value' => AdapterFactory::OFFLINE,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledMethods()
    {
        return $this->gatewayRepository->findActive();
    }
}
