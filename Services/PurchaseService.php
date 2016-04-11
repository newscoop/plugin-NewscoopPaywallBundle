<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Entity\OrderInterface;
use Newscoop\PaywallBundle\Adapter\GatewayAdapter;
use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Purchase service.
 */
class PurchaseService
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var GatewayAdapter
     */
    protected $adapter;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Construct.
     *
     * @param OrderService             $orderService
     * @param PaymentService           $paymentService
     * @param GatewayAdapter           $adapter
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        OrderService $orderService,
        PaymentService $paymentService,
        GatewayAdapter $adapter,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->adapter = $adapter;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Starts the purchase process.
     *
     * @param array $items
     */
    public function startPurchase(array $items = array(), $currency = null)
    {
        $order = $this->orderService->processAndCalculateOrderItems($items, $currency);
        if (!$order->getItems()->isEmpty()) {
            $response = $this->adapter->purchase($order);
            if (!$response) {
                $this->completePurchase($order);
            }

            return $response;
        }
    }

    /**
     * Finishes purchase process.
     *
     * @param array $items
     *
     * @return OderInterface $order
     */
    public function finishPurchase(array $items = array())
    {
        $order = $this->orderService->processAndCalculateOrderItems($items);
        $response = $this->adapter->completePurchase($order);
        $this->completePurchase($order);

        return $response;
    }

    private function completePurchase(OrderInterface $order)
    {
        $this->paymentService->createPayment($order);
        $this->entityManager->persist($order);
        $this->activateOrderItems($order);
        $this->entityManager->flush();
        $this->dispatcher->dispatch(
            PaywallEvents::ORDER_SUBSCRIPTION,
            new GenericEvent($order->getItems()->toArray())
        );
    }

    private function activateOrderItems(OrderInterface $order)
    {
        if ($this->adapter->isOfflineGateway()) {
            return;
        }

        foreach ($order->getItems() as $item) {
            $this->orderService->activateItem($item);
        }
    }
}
