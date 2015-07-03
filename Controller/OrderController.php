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
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\Order;

class OrderController extends BaseController
{
    /**
     * @Route("/{language}/paywall/subscriptions", name="paywall_subscriptions", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $response->setContent($templatesService->fetchTemplate('_paywall/index.tpl'));

        return $response;
    }

    /**
     * @Route("/paywall/subscriptions/order-batch", name="paywall_subscribe_order_batch", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function batchOrderAction(Request $request)
    {
        $items = $request->request->get('batchorder', array());
        $response = new JsonResponse();
        if (empty($items)) {
            $response->setStatusCode(404);

            return $response;
        }

        $em = $this->get('em');
        $orderService = $this->get('newscoop_paywall.services.order');
        $order = $orderService->processAndCalculateOrderItems($items);

        $response->setStatusCode(404);
        if (!$order->getItems()->isEmpty()) {
            $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $order->getItems()->toArray());
            $em->persist($order);
            $em->flush();

            $response->setStatusCode(204);
        }

        return $response;
    }

    /**
     * @Route("/paywall/subscriptions/calculate/{currency}", name="paywall_subscribe_order_calculate", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function calculateAction(Request $request, $currency)
    {
        $em = $this->get('em');
        $items = $request->request->get('batchorder', array());
        $userService = $this->get('user');
        $response = new JsonResponse();
        try {
            $user = $userService->getCurrentUser();
        } catch (\Exception $e) {
            $response->setStatusCode(401);

            return $response;
        }

        $orderService = $this->get('newscoop_paywall.services.order');
        $order = $orderService->processAndCalculateOrderItems($items, $currency);

        return new JsonResponse(array(
            'itemsCount' => $order->countItems(),
            'total' => $order->getTotal(),
            'currency' => $order->getCurrency(),
        ));
    }
}
