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
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\PaywallBundle\Entity\Duration;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;

class OrderController extends BaseController
{
    /**
     * @Route("/paywall/subscriptions/order-batch", name="paywall_subscribe_order_batch", options={"expose"=true})
     *
     * @Method("POST")
     */
    public function batchOrderAction(Request $request)
    {
        $em = $this->get('em');
        $orders = $request->request->get('batchorder');
        $subscriptionService = $this->container->get('paywall.subscription.service');
        $templatesService = $this->get('newscoop.templates.service');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        $response = new Response();
        $orderedSubscriptions = array();
        foreach ($orders as $key => $order) {
            $subscription = $em->getReference('Newscoop\PaywallBundle\Entity\Subscriptions', $key);
            $durationId = $order[0];
            if (!$durationId) {
                continue;
            }

            $ranges = $subscription->getRanges()->filter(function (Duration $duration) use ($durationId) {
                return $duration->getId() == $durationId;
            });

            if (empty($ranges)) {
                $response->setContent($templatesService->fetchTemplate('_paywall/error.tpl', array(
                    'msg' => $translator->trans('paywall.alert.notexists'),
                )));

                return $response;
            }

            $userSubscriptionInactive = $subscriptionService->getOneByUserAndSubscription(
                $user->getId(),
                $subscription->getId(),
                'N'
            );

            $userSubscription = $subscriptionService->getOneByUserAndSubscription(
                $user->getId(),
                $subscription->getId()
            );

            if ($userSubscription || $userSubscriptionInactive) {
                continue;
            }

            $specificationArray = $subscription->getSpecification()->toArray();
            $specification = $specificationArray[0];
            $userSubscription = $subscriptionService->create();
            $subscriptionData = new SubscriptionData(array(
                'userId' => $user,
                'subscriptionId' => $subscription,
                'publicationId' => $request->get('publication_id') ?: $specification->getPublication(),
                'toPay' => $subscription->getPrice(),
                'duration' => $ranges[0],
                'currency' => $subscription->getCurrency(),
                'type' => 'T',
                'active' => false,
            ), $userSubscription);

            $userSubscription = $subscriptionService->update($userSubscription, $subscriptionData);
            $subscriptionService->save($userSubscription, false);
            $orderedSubscriptions[] = $userSubscription;
        }

            // find subscription by $key
            // check if duration with id $order[0] exists
            // if exists subscribe  for this subscription
            // else throw error, rollback all changes, dont flush

        $this->dispatchNotificationEvent(PaywallEvents::ORDER_SUBSCRIPTION, $orderedSubscriptions);

        $response->setContent($templatesService->fetchTemplate('_paywall/success.tpl', array(
            'subscriptions' => $orderedSubscriptions,
        )));

        return $response;
    }
}
