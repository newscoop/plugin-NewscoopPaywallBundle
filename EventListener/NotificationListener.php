<?php

/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\PaywallBundle\Services\NotificationsService;
use Newscoop\PaywallBundle\Notifications\Emails;
use Newscoop\Entity\User;
use Newscoop\PaywallBundle\Entity\UserSubscription;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Notifications listener
 */
class NotificationListener
{
    /**
     * Notifications service
     *
     * @var NotificationsService
     */
    protected $notificationsService;

    public function __construct(NotificationsService $notificationsService)
    {
        $this->notificationsService = $notificationsService;
    }

    /**
     * Sends confirmation email to user
     *
     * @param GenericEvent $event
     */
    public function sendUserNotificationEmail(GenericEvent $event)
    {
        $subscription = $event->getSubject();
        $this->sendValidatedNotification(Emails::USER_CONFIRMATION, $subscription);
    }

    /**
     * Sends confirmation email with informations
     * about created subscription for user, by admin
     * in backend.
     *
     * @param GenericEvent $event
     */
    public function sendAdminCreatedNotification(GenericEvent $event)
    {
        $subscription = $event->getSubject();
        $this->sendValidatedNotification(Emails::ADMIN_CREATED_CONFIRMATION, $subscription);
    }

    /**
     * Sends notification email to admin
     *
     * @param GenericEvent $event
     */
    public function sendSubscriptionNotificationEmail(GenericEvent $event)
    {
        $subscription = $event->getSubject();
        $subscription = $event->getSubject();
        if (!$subscription instanceof UserSubscription) {
            throw new UnexpectedTypeException(
                $subscription,
                'PaywallBundle\Entity\UserSubscription'
            );
        }

        $user = $subscription->getUser();
        $this->notificationsService->sendNotification(
            Emails::SUBSCRIPTION_CONFIRMATION,
            array(),
            array(
                'subscription' => $subscription,
                'user' => $user,
            )
        );
    }

    /**
     * Sends confirmation email to user when subscription
     * status is changed in paywall admin panel.
     *
     * @param GenericEvent $event
     */
    public function sendUserSubscriptionStatusChangeEmail(GenericEvent $event)
    {
        $subscription = $event->getSubject();
        $this->sendValidatedNotification(Emails::SUBSCRIPTION_STATUS, $subscription);
    }

    /**
     * Sends an emails with the informations about expiring
     * subscription.
     *
     * @param GenericEvent $event
     */
    public function sendSubscriptionExpirationEmail(GenericEvent $event)
    {
        $subscription = $event->getSubject();
        $this->sendValidatedNotification(Emails::SUBSCRIPTION_EXPIRATION, $subscription);
        if (!$subscription->getNotifySentLevelOne()) {
            $this->notificationsService->setSentDateTimeOnLevelOne($subscription);
        } else {
            $this->notificationsService->setSentDateTimeOnLevelTwo($subscription);
        }
    }

    private function sendValidatedNotification($code, $subscription)
    {
        if (!$subscription instanceof UserSubscription) {
            throw new UnexpectedTypeException(
                $subscription,
                'PaywallBundle\Entity\UserSubscription'
            );
        }

        $user = $subscription->getUser();
        $this->notificationsService->sendNotification(
            $code,
            array($user->getEmail()),
            array(
                'subscription' => $subscription,
                'user' => $user,
            )
        );
    }
}
