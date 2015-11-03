<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Entity\PaymentInterface;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Doctrine\ORM\EntityManager;

/**
 * Payment service.
 */
class PaymentService
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create an payment.
     *
     * @param OrderInterface $order
     */
    public function createPayment(OrderInterface $order)
    {
        $payment = $this->getRepository()->createNew();
        $payment->setOrder($order);
        $payment->setMethod('PayPal_Express');
        $payment->setAmount($order->getTotal());
        $payment->setCurrency($order->getCurrency());
        $payment->setState(PaymentInterface::STATE_COMPLETED);
        $order->addPayment($payment);
    }

    public function getRepository()
    {
        return $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Payment');
    }
}
