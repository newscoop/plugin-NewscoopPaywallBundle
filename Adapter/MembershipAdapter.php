<?php

namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Services\PaywallService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Adapter\PaywallAdapterInterface;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use SimpleXmlElement;

class MembershipAdapter implements PaywallAdapterInterface
{   
    private $subscriptionService;

    private $request;

    /**
     * @var array
     */
    private $activeStatusCodes = array(1, 2, 3);

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

                $subscription = $this->subscriptionService->getOneByUser($user);

                if ($dmpro->isCustomer($customerId) && $isValid) {
                    if ($subscription->getSubscription()->getName() === $this->getSubscriptionName($customer)) {
                        if (!$user->getAttribute('customer_id')) {
                            $user->addAttribute('customer_id', $customerId);
                            $subscriptionData = new SubscriptionData(array(
                                'active' => true,
                                'type' => 'P'
                            ), $subscription);
                            $subscription = $this->subscriptionService->update($subscription, $subscriptionData);
                            $em->flush();

                            $this->subscriptionService->deactivateTrial($user);

                            return array('status' => true, 'validCode' => true, 'thanks' => true);
                        }
                    } else {
                        //aktivate new
                        $toActivate = $this->subscriptionService->getSubscriptionToActivate($user);
                        // get lastest inactive subscription
                        $toActivate->setActive(true);
                        // deactivate current one
                        $subscription->setActive(false);
                        $em->flush();

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

    /**
     * Find subscription name
     *
     * @param SimpleXmlElement $subscriber
     * @return string
     */
    public function getSubscriptionName(SimpleXmlElement $subscriber)
    {
        $subscriptions = $subscriber->subscriptions->xpath('subscription');

        $statusCodes = $this->activeStatusCodes;
        $activeSubscriptions = array_filter($subscriptions, function ($subscription) use ($statusCodes) {
            return in_array((int) $subscription->statusCode, $statusCodes);
        });

        $dates = array_map(function ($subscription) {
            $paidUntil = $subscription->xpath('expectedPaidUntil');
            $description = $subscription->xpath('description');

            if (empty($paidUntil)) {
                $paidUntil = $subscription->paidUntil;
                $description = $subscription->description;
            } else {
                $paidUntil = array_pop($paidUntil);
                $description = array_pop($description);
            }

            return array(\DateTime::createFromFormat('Ymd', $paidUntil)->format('Y-m-d'), (string) $description);
        }, $activeSubscriptions);

        if (!empty($dates)) {
            $maxDate = max($dates);
            unset($maxDate[0]);

            return $maxDate[1];
        }
    }
}
