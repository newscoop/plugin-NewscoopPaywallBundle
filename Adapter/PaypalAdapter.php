<?php

/**
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\Services\SubscriptionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaypalAdapter extends AbstractAdapter
{
    public function process()
    {
        $request = new \PayPal\Ipn\Request\Curl();
        $listener = new \PayPal\Ipn\Listener($this->request);
        $listener->setMode('sandbox');

        try {
            $status = $listener->verifyIpn();
        } catch (\Exception $e) {
            // log error mesage
            $error = $e->getMessage();
        }

        if ($status) {
            $custom = explode('__', $this->request->get('custom', array()));
            $subscription = $this->subscriptionService->getOneById($custom[0]);
            $subscriptionData = new \Newscoop\Subscription\SubscriptionData(array(
                'active' => true,
            ), $subscription);
            $subscription = $this->subscriptionService->update($subscription, $subscriptionData);
            $this->subscriptionService->save($subscription);
        } else {
            //invalid...
            $report = $listener->getStatusReport();
        }

        return new Response('OK');
    }
}
