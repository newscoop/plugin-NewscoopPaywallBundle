<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Entity\PaymentInterface;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Newscoop\PaywallBundle\Adapter\AdapterFactory;
use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Adapter\GatewayAdapter;
use Newscoop\PaywallBundle\Provider\MethodProviderInterface;

/**
 * Payment service.
 */
class PaymentService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var GatewayAdapter
     */
    protected $adapter;

    /**
     * @var MethodProviderInterface
     */
    protected $paymentMethodProvider;

    /**
     * Construct.
     *
     * @param EntityManager           $entityManager
     * @param GatewayAdapter          $adapter
     * @param MethodProviderInterface $paymentMethodProvider
     */
    public function __construct(
        EntityManager $entityManager,
        GatewayAdapter $adapter,
        MethodProviderInterface $paymentMethodProvider
    ) {
        $this->entityManager = $entityManager;
        $this->adapter = $adapter;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * Create an payment.
     *
     * @param OrderInterface $order
     */
    public function createPayment(OrderInterface $order)
    {
        $enabledAdapter = $this->paymentMethodProvider->getActiveMethod();
        $payment = $this->getRepository()->createNew();
        $payment->setOrder($order);
        $payment->setMethod($this->adapter->isOfflineGateway() ? AdapterFactory::OFFLINE : $enabledAdapter->getValue());
        $payment->setAmount($order->getTotal());
        $payment->setCurrency($order->getCurrency());
        $payment->setState(PaymentInterface::STATE_COMPLETED);
        if ($this->adapter->isOfflineGateway()) {
            $payment->setState(PaymentInterface::STATE_PENDING);
        }

        $order->addPayment($payment);
    }

    /**
     * Get Payment repository.
     *
     * @return Newscoop\PaywallBundle\Repository\PaymentRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Payment');
    }
}
