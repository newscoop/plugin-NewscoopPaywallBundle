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
     * Entity Manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EmailService             $emailService
     * @param SystemPreferencesService $systemPreferences
     */
    public function __construct(
        EmailService $emailService,
        SystemPreferencesService $systemPreferences,
        TemplatesService $templatesService,
        PlaceholdersService $placeholdersService,
        EntityManager $em
    ) {
        $this->emailService = $emailService;
        $this->systemPreferences = $systemPreferences;
        $this->templatesService = $templatesService;
        $this->placeholdersService = $placeholdersService;
        $this->em = $em;
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
            $recipients
        );
    }

    /**
     * Sets notify sent flag for given user subscription.
     *
     * @param UserSubscription $subscription User subscription
     * @param Boolean          $flag         true or false
     */
    public function setNotifySentFlag(UserSubscription $subscription, $flag = true)
    {
        $subscription->setNoticeSent($flag);
        $this->em->flush();
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
                break;
        }

        return $message;
    }
}
