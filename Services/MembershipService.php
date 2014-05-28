<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Services\PaywallService;
use Newscoop\Services\EmailService;
use Newscoop\Services\UserService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

/**
 * Membership Service
 */
class MembershipService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaywallService
     */
    protected $subscriptionService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var Newscoop\Services\TemplatesService
     */
    protected $templatesService;

    /**
     * @var Newscoop\Services\PlaceholdersService
     */
    protected $placeholdersService;

    /**
     * @var Zend_Auth
     */
    protected $zendRouter;

    /**
     * @var NewscoopBundle\Services\SystemPreferencesService
     */
    protected $preferencesService;

    /**
     * Construct
     * @param EntityManager                                    $em
     * @param PaywallService                                   $subscriptionService
     * @param UserService                                      $userService
     * @param EmailService                                     $emailService
     * @param Newscoop\Services\TemplatesService               $templatesService
     * @param Newscoop\Services\PlaceholdersService            $placeholdersService
     * @param Zend_Auth                                        $zendRouter
     * @param NewscoopBundle\Services\SystemPreferencesService $preferencesService
     */
    public function __construct(EntityManager $em, PaywallService $subscriptionService, UserService $userService,
        EmailService $emailService, $templatesService, $placeholdersService, $zendRouter, $preferencesService)
    {
        $this->em = $em;
        $this->subscriptionService = $subscriptionService;
        $this->userService = $userService;
        $this->emailService = $emailService;
        $this->templatesService = $templatesService;
        $this->placeholdersService = $placeholdersService;
        $this->zendRouter = $zendRouter;
        $this->preferencesService = $preferencesService;
    }

    /**
     * Send membership notification email
     *
     * @param  Request $request                 Request object
     * @param  string  $newSubscriptionName     New membership name
     * @param  string  $currentSubscriptionName Current membership name
     * @param  float   $toPay                   To pay
     * @param  boolean $status                  Number status
     * @param  boolean $toUser                  Send email to user also
     * @param  integer $leftTrialDays           Left days of trial
     *
     * @return void
     */
    public function sendEmail(Request $request, $newSubscriptionName, $currentSubscriptionName, $toPay, $status, $toUser = false, $leftTrialDays = null)
    {
        $user = $this->userService->getCurrentUser();
        $smarty = $this->templatesService->getSmarty();
        $smarty->assign('user', new \MetaUser($user));
        $smarty->assign('customerId', $user->getAttribute('customer_id'));
        $smarty->assign('newSubscriptionName', $newSubscriptionName);
        $smarty->assign('currentSubscriptionName', $currentSubscriptionName);
        $smarty->assign('toPay', $toPay);
        $smarty->assign('status', $status);
        $smarty->assign('street', $user->getStreet());
        $smarty->assign('postal', $user->getPostal());
        $smarty->assign('city', $user->getCity());
        $smarty->assign('state', $user->getState());
        $smarty->assign('leftTrialDays', $leftTrialDays);
        $smarty->assign('userLink', $request->getUriForPath($this->zendRouter->assemble(array('controller' => 'user', 'action' => 'profile')) . '/' . $user->getUsername()));
        if ($toUser) {
            $message = $this->templatesService->fetchTemplate("email_membership_user.tpl");
            $this->emailService->send($this->placeholdersService->get('subject'), $message, array($user->getEmail()), array($this->preferencesService->PaywallMembershipNotifyEmail));
        }

        $message = $this->templatesService->fetchTemplate("email_membership_staff.tpl");
        $this->emailService->send($this->placeholdersService->get('subject'), $message, array($this->preferencesService->PaywallMembershipNotifyEmail));
    }

    /**
     * Send notification with expiring memberships to users
     *
     * @param  UserSubscription $userSubscription User subscription object
     *
     * @return void
     */
    public function expiringSubscriptionNotifyEmail($userSubscription)
    {
        $smarty = $this->templatesService->getSmarty();
        $smarty->assign('user', new \MetaUser($userSubscription->getUser()));
        $smarty->assign('customerId', $userSubscription->getUser()->getAttribute('customer_id'));
        $smarty->assign('subscription', $userSubscription);

        $message = $this->templatesService->fetchTemplate("email_membership_expire.tpl");
        $this->emailService->send($this->placeholdersService->get('subject'), $message, array($userSubscription->getUser()));
    }

    /**
     * Calculates diffrence between subscriptions prices when upgrading/downgrading
     *
     * @param  UserSubscription $currentSubscription Current subscription
     * @param  UserSubscription $newSubscription     New subscription
     *
     * @return string
     */
    public function calculatePriceDiff($currentSubscription, $newSubscription)
    {
        $currentToPay = (int) $currentSubscription->getToPay();
        $newToPay = (int) $newSubscription->getToPay();

        $sum = 0;
        if ($newToPay > $currentToPay) {
            $sum = $newToPay - $currentToPay;
        }

        return $sum;
    }

    /**
     * Calculates days left to trial expiration when trial is still active
     *
     * @param  DateTime $trialExpireDate Trial expiration date
     *
     * @return string
     */
    public function calculateTrialDiff($trialExpireDate)
    {
        $now = new \DateTime();
        $diff = $now->diff($trialExpireDate);

        return $diff->format("%R%a");
    }

    /**
     * Checks if user submitted more than 3 membership requests in a row on the same day (spam protection)
     *
     * @return boolean
     */
    public function isSpam()
    {
        $now = new \DateTime();
        $user = $this->userService->getCurrentUser();
        $qb = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->createQueryBuilder('s');

        $qb
            ->select('count(s)')
            ->where('s.user = :user')
            ->andWhere($qb->expr()->gte('s.created_at', ':yesterday'))
            ->andWhere('s.active = :status')
            ->setParameters(array(
                'user' => $user,
                'status' => 'N',
                'yesterday' => $now->modify('-1 day'),
            ))
            ->orderBy('s.created_at', 'desc');

        $subscriptionsCount = (int) $qb->getQuery()->getSingleScalarResult();

        if ($subscriptionsCount > 3) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user details (Address) is filled in
     *
     * @param  Newscoop\Entity\User $user User object
     *
     * @return boolean
     */
    public function isUserAddressFilledIn($user)
    {
        if (!$user->getFirstName() || !$user->getLastName() || !$user->getPostal() || !$user->getStreet() || !$user->getCity() || !$user->getState()) {
            return false;
        }

        return true;
    }
}
