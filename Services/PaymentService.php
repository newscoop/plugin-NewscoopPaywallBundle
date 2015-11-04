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
     * Construct.
     *
     * @param EntityManager $entityManager
     */
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
        $enabledAdapter = $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
            ->findOneBy(array(
                'isActive' => true,
            ));

        $payment = $this->getRepository()->createNew();
        $payment->setOrder($order);
        $payment->setMethod($enabledAdapter->getValue());
        $payment->setAmount($order->getTotal());
        $payment->setCurrency($order->getCurrency());
        $payment->setState(PaymentInterface::STATE_COMPLETED);
        if ($enabledAdapter->getValue() === AdapterFactory::OFFLINE) {
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
