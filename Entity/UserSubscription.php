<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Newscoop\Entity\Publication;
use Newscoop\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription entity
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\UserSubscriptionRepository")
 * @ORM\Table(name="plugin_paywall_user_subscriptions")
 */
class UserSubscription
{
    const TYPE_PAID = 'P';
    const TYPE_PAID_NOW = 'PN';
    const TYPE_TRIAL = 'T';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="Id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="IdUser", referencedColumnName="Id")
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Subscriptions")
     * @ORM\JoinColumn(name="IdSubscription", referencedColumnName="id")
     * @var Newscoop\PaywallBundle\Entity\Subscriptions
     */
    protected $subscription;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\Publication")
     * @ORM\JoinColumn(name="IdPublication", referencedColumnName="Id")
     * @var Newscoop\Entity\Publication
     */
    protected $publication;

    /**
     * @ORM\Column(type="decimal", name="ToPay")
     * @var float
     */
    protected $toPay = 0.0;

    /**
     * @ORM\Column(name="Type")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="Currency")
     * @var string
     */
    protected $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Trial")
     * @ORM\JoinColumn(name="trial_id", referencedColumnName="id")
     * @var Newscoop\PaywallBundle\Entity\Trial
     */
    protected $trial;

    /**
     * Subscription status visible for admin
     * @ORM\Column(name="Active")
     * @var string
     */
    protected $active;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var DateTime
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", name="expire_at", nullable=true)
     * @var DateTime
     */
    protected $expire_at;

    /**
     * Custom field
     * @ORM\Column(type="boolean", name="custom")
     * @var boolean
     */
    protected $custom;

    /**
     * Second custom field
     * @ORM\Column(type="boolean", name="custom_2")
     * @var boolean
     */
    protected $customOther;

    /**
     * To hide from users totally
     * @ORM\Column(type="boolean", name="is_active")
     * @var boolean
     */
    protected $is_active;

    /**
     * @ORM\Column(type="datetime", name="notify_sent_first", nullable=true)
     * @var \DateTime
     */
    protected $notifySentLevelOne;

    /**
     * @ORM\Column(type="datetime", name="notify_sent_second", nullable=true)
     * @var \DateTime
     */
    protected $notifySentLevelTwo;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     * @var \DateTime
     */
    protected $updated;

    public function __construct()
    {
        $this->currency = '';
        $this->active = 'N';
        $this->created_at = new \DateTime();
        $this->is_active = false;
        $this->custom = false;
        $this->customOther = false;
        $this->type = self::TYPE_PAID;
        $this->notifySentLevelOne = null;
        $this->notifySentLevelTwo = null;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Set subscription
     *
     * @param  Newscoop\PaywallBundle\Entity\Subscriptions $subscription
     * @return void
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get subscription
     *
     * @return Newscoop\PaywallBundle\Entity\Subscription_specification
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set user
     *
     * @param  Newscoop\Entity\User $user
     * @return void
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Newscoop\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set publication
     *
     * @param  Newscoop\Entity\Publication  $publication
     * @return Newscoop\Entity\Subscription
     */
    public function setPublication(Publication $publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication
     *
     * @return Newscoop\Entity\Publication
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Get publication name
     *
     * @return string
     */
    public function getPublicationName()
    {
        return $this->publication->getName();
    }

    /**
     * Get publication id
     *
     * @return int
     */
    public function getPublicationId()
    {
        return $this->publication->getId();
    }

    /**
     * Set to pay
     *
     * @param  float                        $toPay
     * @return Newscoop\Entity\Subscription
     */
    public function setToPay($toPay)
    {
        $this->toPay = (float) $toPay;

        return $this;
    }

    /**
     * Get to pay
     *
     * @return float
     */
    public function getToPay()
    {
        return (float) $this->toPay;
    }

    /**
     * Set type
     *
     * @param  string                       $type
     * @return Newscoop\Entity\Subscription
     */
    public function setType($type)
    {
        $this->type = strtoupper($type) === self::TYPE_TRIAL ? self::TYPE_TRIAL : self::TYPE_PAID;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Test if is trial
     *
     * @return bool
     */
    public function isTrial()
    {
        return $this->type === self::TYPE_TRIAL;
    }

    /**
     * Set active
     *
     * @param  bool                         $active
     * @return Newscoop\Entity\Subscription
     */
    public function setActive($active)
    {
        $this->active = ((bool) $active) ? 'Y' : 'N';

        return $this;
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return strtoupper($this->active) === 'Y';
    }

    /**
     * Get currency
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     * @return string
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get trial
     * @return Trial
     */
    public function getTrial()
    {
        return $this->trial;
    }

    /**
     * Set trial
     * @return Trial
     */
    public function setTrial($trial)
    {
        $this->trial = $trial;

        return $this;
    }

    /**
     * Get create date
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set create date
     *
     * @param  datetime $created_at
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get expire date
     *
     * @return datetime
     */
    public function getExpireAt()
    {
        return $this->expire_at;
    }

    /**
     * Set expire date
     *
     * @param  datetime $expire_at
     * @return datetime
     */
    public function setExpireAt(\DateTime $expire_at = null)
    {
        $this->expire_at = $expire_at;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set status
     *
     * @param  boolean $is_active
     * @return boolean
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Gets the Custom field.
     *
     * @return boolean
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Sets the Custom field.
     *
     * @param boolean $custom the custom
     *
     * @return self
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * Gets the Second custom field.
     *
     * @return boolean
     */
    public function getCustomOther()
    {
        return $this->customOther;
    }

    /**
     * Sets the Second custom field.
     *
     * @param boolean $customOther the custom other
     *
     * @return self
     */
    public function setCustomOther($customOther)
    {
        $this->customOther = $customOther;

        return $this;
    }

    /**
     * Gets the value of updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Sets the value of updated.
     *
     * @param DateTime $updated the updated
     *
     * @return self
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Gets the value of notifySentLevelOne.
     *
     * @return \DateTime
     */
    public function getNotifySentLevelOne()
    {
        return $this->notifySentLevelOne;
    }

    /**
     * Sets the value of notifySentLevelOne.
     *
     * @param \DateTime $notifySentLevelOne the notify sent level one
     *
     * @return self
     */
    public function setNotifySentLevelOne(\DateTime $notifySentLevelOne)
    {
        $this->notifySentLevelOne = $notifySentLevelOne;

        return $this;
    }

    /**
     * Gets the value of notifySentLevelTwo.
     *
     * @return \DateTime
     */
    public function getNotifySentLevelTwo()
    {
        return $this->notifySentLevelTwo;
    }

    /**
     * Sets the value of notifySentLevelTwo.
     *
     * @param \DateTime $notifySentLevelTwo the notify sent level two
     *
     * @return self
     */
    public function setNotifySentLevelTwo(\DateTime $notifySentLevelTwo)
    {
        $this->notifySentLevelTwo = $notifySentLevelTwo;

        return $this;
    }
}
