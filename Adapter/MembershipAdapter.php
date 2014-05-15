<?php

namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Services\PaywallService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Adapter\PaywallAdapterInterface;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;

class MembershipAdapter implements PaywallAdapterInterface
{   
    private $subscriptionService;

    private $request;

    public function setRequest(Request $request) {
        $this->request = $request;
    }

    public function __construct(PaywallService $subscriptionService) {
        $this->subscriptionService = $subscriptionService;
    }

    public function proccess()
    {
        $params = $this->request->request->all();
        $customerId = null;
         $subscriptionId = null;
        if (array_key_exists('membershipForm', $params)) {
            $customerId = $params['membershipForm']['customer_id'];
            $subscriptionId = $params['membershipForm']['membershipType'];
        }
        $em = \Zend_Registry::get('container')->getService('em');
        $dmpro = \Zend_Registry::get('container')->getService('newscoop_tageswochemobile_plugin.dmpro_service');
        $userService = \Zend_Registry::get('container')->getService('user');
        $user = $userService->getCurrentUser();
        if (!$customerId) {
            $customerId = $user->getAttribute('customer_id');
        }

        try {

            if ($customerId !== '' && $customerId != null) {
                $customer = $dmpro->findByCustomer($customerId);
                $paidUntil = $dmpro->getMax($customer);
                if ($paidUntil >= new \DateTime()) {
                    $isValid = true;
                } else {
                    $isValid = false;
                }

                if ($dmpro->isCustomer($customerId) && $isValid) {
                    if (!$user->getAttribute('customer_id')) {
                        $user->addAttribute('customer_id', $customerId);
                        $subscription = $this->subscriptionService->getOneByUser($user);
                        $subscriptionData = new SubscriptionData(array(
                            'active' => 'Y',
                            'type' => 'P'
                        ), $subscription);
                        $subscription = $this->subscriptionService->update($subscription, $subscriptionData);
                        $em->flush();

                        $this->subscriptionService->deactivateTrial($user);

                        return array('status' => true, 'validCode' => true, 'thanks' => true);
                    }

                    return array('status' => true, 'validCode' => true, 'thanks' => false);
                }
            }

            return array('status' => false, 'validCode' => false);
        } catch (\Exception $e) {
            if ($dmpro->isCustomer($user->getAttribute('customer_id'))) {
                return array('status' => true, 'validCode' => false);
            }

            return array('status' => false, 'validCode' => false);
        }
    }
}
