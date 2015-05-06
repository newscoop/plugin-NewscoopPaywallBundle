<?php

/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\Services\EmailService;
use Newscoop\NewscoopBundle\Services\SystemPreferencesService;
use Newscoop\Services\PlaceholdersService;
use Newscoop\Services\TemplatesService;
use Newscoop\PaywallBundle\Notifications\Emails;
use Newscoop\PaywallBundle\Meta\MetaSubscription;
use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\PaywallBundle\Entity\UserSubscription;

/**
 * Notifications service
 */
class NotificationsService
{
    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var SystemPreferencesService
     */
    protected $systemPreferences;

    /**
     * @var TemplatesService
     */
    protected $templatesService;

    /**
     * @var PlaceholdersService
     */
    protected $placeholdersService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    protected $dispatcher;

    /**
     * @param EmailService                  $emailService
     * @param SystemPreferencesService      $systemPreferences
     * @param TemplatesService              $templatesService
     * @param PlaceholdersService           $placeholdersService
     * @param EntityManager                 $em
     * @param ContainerAwareEventDispatcher $dispatcher
     */
    public function __construct(
        EmailService $emailService,
        SystemPreferencesService $systemPreferences,
        TemplatesService $templatesService,
        PlaceholdersService $placeholdersService,
        EntityManager $em,
        $dispatcher
    ) {
        $this->emailService = $emailService;
        $this->systemPreferences = $systemPreferences;
        $this->templatesService = $templatesService;
        $this->placeholdersService = $placeholdersService;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Sends email notifications
     *
     * @param string                                $code             Email message type
     * @param Newscoop\Entity\User                  $user             User object
     * @param PaywallBundle\Entity\UserSubscription $userSubscription User's subscription
     */
    public function sendNotification($code, array $recipients = array(), array $data = array())
    {
        if ($this->systemPreferences->PaywallEmailNotifyEnabled != '1') {
            return;
        }

        $now = new \DateTime('now');
        $smarty = $this->templatesService->getSmarty();
        $smarty->assign('user', new \MetaUser($data['user']));
        $smarty->assign('userSubscription', new MetaSubscription($data['subscription']));

        try {
            $message = $this->loadProperMessageTemplateBy($code);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $recipients = !empty($recipients) ? $recipients : $this->systemPreferences->PaywallMembershipNotifyEmail;
        $this->emailService->send(
            $this->placeholdersService->get('subject'),
            $message,
            $recipients,
            $this->systemPreferences->PaywallMembershipNotifyFromEmail
        );
    }

    private function loadProperMessageTemplateBy($code)
    {
        switch ($code) {
            case Emails::USER_CONFIRMATION:
                $message = $this->templatesService->fetchTemplate(
                    "_paywall/email_notify_user.tpl"
                );
                break;
            case Emails::SUBSCRIPTION_CONFIRMATION:
                $message = $this->templatesService->fetchTemplate(
                    "_paywall/email_notify_admin.tpl"
                );
                break;
            case Emails::SUBSCRIPTION_STATUS:
                $message = $this->templatesService->fetchTemplate(
                    "_paywall/email_subscription_status.tpl"
                );
                break;
            case Emails::SUBSCRIPTION_EXPIRATION:
                $message = $this->templatesService->fetchTemplate(
                    "_paywall/email_subscription_expiration.tpl"
                );
                $message = 'test';
                break;
        }

        return $message;
    }

    /**
     * Sets notify sent flag for given user subscription.
     * Level one of the notifications, for instance,
     * sends notifications 7 days before expiration.
     *
     * @param UserSubscription $subscription User subscription
     * @param \DateTime        $datetime     Date time when notify has been sent
     */
    public function setSentDateTimeOnLevelOne(UserSubscription $subscription, $datetime = null)
    {
        $subscription->setNotifySentLevelOne($datetime ?: new \DateTime('now'));
        $this->em->flush();
    }

    /**
     * Sets notify sent flag for given user subscription.
     * Level two of the notifications, for instance,
     * sends notifications 3 days before expiration.
     *
     * @param UserSubscription $subscription User subscription
     * @param \DateTime        $datetime     Date time when notify has been sent
     */
    public function setSentDateTimeOnLevelTwo(UserSubscription $subscription, $datetime = null)
    {
        $subscription->setNotifySentLevelTwo($datetime ?: new \DateTime('now'));
        $this->em->flush();
    }

    /**
     * Processes expiring subscriptions and sends email
     * notifications to users.
     *
     * @param \DateTime $now                Current date time
     * @param integer   $subscriptionsCount Subscriptions count
     */
    public function processExpiringSubscriptions($now, $notify, $count = 0, $days = 7)
    {
        if ($count === 0) {
            $count = $this->getExpiringSubscriptionsCount($now);
        }

        $batch = 100;
        $steps = ($count > $batch) ? ceil($count / $batch) : 1;
        for ($i = 0; $i < $steps; $i++) {
            $offset = $i * $batch;

            $query = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
                ->getExpiringSubscriptions($offset, $batch, $now, $notify, $days);

            $expiringSubscriptions = $query->getResult();
            $this->dispatchNotificationSend($expiringSubscriptions);
        }
    }

    /**
     * Gets expiring subscriptions count.
     *
     * @param  \DateTime $now Current date time
     * @return integer
     */
    public function getExpiringSubscriptionsCount($now, $notify, $days = 7)
    {
        $query = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->getExpiringSubscriptionsCount($now, $notify, $days);

        return (int) $query->getSingleScalarResult();
    }

    private function dispatchNotificationSend($expiringSubscriptions)
    {
        foreach ($expiringSubscriptions as $key => $subscription) {
            $this->dispatcher->dispatch(
                PaywallEvents::SUBSCRIPTION_EXPIRATION,
                new GenericEvent($subscription)
            );
        }
    }
}
