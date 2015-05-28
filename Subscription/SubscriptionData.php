<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Subscription;

use Newscoop\PaywallBundle\Entity\UserSubscription;

/**
 * Subscription Data holder.
 */
class SubscriptionData
{
    /**
     * Subscription Class.
     *
     * @var Subscription
     */
    public $subscription;

    /**
     * User id.
     *
     * @var int
     */
    public $userId;

    /**
     * Subscription id.
     *
     * @var int
     */
    public $subscriptionId;

    /**
     * Publication Id.
     *
     * @var int
     */
    public $publicationId;

    /**
     * To pay value.
     *
     * @var decimal
     */
    public $toPay;

    /**
     * Subscription start date.
     *
     * @var \DateTime
     */
    public $startDate;

    /**
     * How long subscription should be valid.
     *
     * @var int
     */
    public $days;

    /**
     * Subscription duration.
     *
     * @var Newscoop\PaywallBundle\Entity\Duration
     */
    public $duration;

    /**
     * How long subscription will be valid.
     *
     * @var int
     */
    public $paidDays;

    /**
     * Currency.
     *
     * @var string
     */
    public $currency;

    /**
     * Subscription status.
     *
     * @var bool
     */
    public $active;

    /**
     * Status to hide it globally.
     *
     * @var bool
     */
    public $is_active;

    public $mainSubscriptionId;

    /**
     * Subscription type.
     * 'T' for Trial subscription, 'P' for paid subscription or 'PN' for paid now subscriptions.
     *
     * @var string
     */
    public $type = 'P';

    public function __construct(array $data, UserSubscription $subscription = null)
    {
        // process data array
        $this->startDate = new \DateTime();
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        // fill paidDays with days value
        /*if (!$this->paidDays) {
            $this->paidDays = $this->days;
        }*/

        if (!$subscription) {
            $this->subscription = new UserSubscription();
        } else {
            $this->subscription = $subscription;
        }

        return $this;
    }
}
