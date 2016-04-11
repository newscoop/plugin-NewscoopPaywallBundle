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

class OrderController extends BaseController
{
    /**
     * @Route("/{language}/paywall/subscriptions", name="paywall_subscriptions", options={"expose"=true})
     *
     * @Method("GET")
     */
    public function indexAction()
    {
        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $response->setContent($templatesService->fetchTemplate('_paywall/index.tpl'));

        return $response;
    }

    /**
     * @Route("/paywall/subscriptions/calculate/{currency}", name="paywall_subscribe_order_calculate", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function calculateAction(Request $request, $currency)
    {
        $items = $request->request->get('batchorder', array());
        $userService = $this->get('user');
        $response = new JsonResponse();
        try {
            $userService->getCurrentUser();
        } catch (\Exception $e) {
            $response->setStatusCode(401);

            return $response;
        }

        try {
            $orderService = $this->get('newscoop_paywall.services.order');
            $order = $orderService->processAndCalculateOrderItems($items, $currency);
        } catch (\Exception $e) {
            $response->setStatusCode(422);

            return $response;
        }

        return new JsonResponse(array(
            'itemsCount' => $order->countItems(),
            'total' => $order->getTotal(),
            'currency' => $order->getCurrency(),
        ));
    }
}
