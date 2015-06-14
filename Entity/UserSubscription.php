<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Newscoop\Entity\Publication;
use Newscoop\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Newscoop\PaywallBundle\Discount\DiscountableInterface;

/**
 * Subscription entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\UserSubscriptionRepository")
 * @ORM\Table(name="plugin_paywall_user_subscriptions")
 */
class UserSubscription implements DiscountableInterface, ProlongableItemInterface
{
    const TYPE_PAID = 'P';
    const TYPE_PAID_NOW = 'PN';
    const TYPE_TRIAL = 'T';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="Id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="IdUser", referencedColumnName="Id")
     *
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Subscriptions")
     * @ORM\JoinColumn(name="IdSubscription", referencedColumnName="id")
     *
     * @var Newscoop\PaywallBundle\Entity\Subscriptions
     */
    protected $subscription;

    /**
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="items")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     *
     * @var Order
     */
    protected $order;

    /**
     * @ORM\Column(type="integer", name="discount_total")
     *
     * @var int
     */
    protected $discountTotal = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\Publication")
     * @ORM\JoinColumn(name="IdPublication", referencedColumnName="Id")
     *
     * @var Newscoop\Entity\Publication
     */
    protected $publication;

    /**
     * @ORM\Column(type="decimal", name="ToPay")
     *
     * @var float
     */
    protected $toPay = 0.0;

    /**
     * @ORM\Column(name="Type")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="Currency")
     *
     * @var string
     */
    protected $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Trial")
     * @ORM\JoinColumn(name="trial_id", referencedColumnName="id")
     *
     * @var Newscoop\PaywallBundle\Entity\Trial
     */
    protected $trial;

    /**
     * Subscription status visible for admin.
     *
     * @ORM\Column(name="Active")
     *
     * @var string
     */
    protected $active;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var DateTime
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", name="expire_at", nullable=true)
     *
     * @var DateTime
     */
    protected $expire_at;

    /**
     * Custom field.
     *
     * @ORM\Column(type="boolean", name="custom")
     *
     * @var bool
     */
    protected $custom;

    /**
     * Second custom field.
     *
     * @ORM\Column(type="boolean", name="custom_2")
     *
     * @var bool
     */
    protected $customOther;

    /**
     * To hide from users totally.
     *
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $is_active;

    /**
     * Is prolonged?
     *
     * @ORM\Column(type="boolean", name="prolonged")
     *
     * @var bool
     */
    protected $prolonged = false;

    /**
     * @ORM\Column(type="datetime", name="notify_sent_first", nullable=true)
     *
     * @var \DateTime
     */
    protected $notifySentLevelOne;

    /**
     * @ORM\Column(type="datetime", name="notify_sent_second", nullable=true)
     *
     * @var \DateTime
     */
    protected $notifySentLevelTwo;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     *
     * @var \DateTime
     */
    protected $updated;

    /**
     * @ORM\Column(type="array", name="duration")
     *
     * @var array
     */
    protected $duration;

    /**
     * @ORM\OneToMany(targetEntity="Modification", mappedBy="orderItem", orphanRemoval=true, cascade={"all"})
     *
     * @var ArrayCollection
     */
    protected $modifications;

    /**
     * @ORM\ManyToMany(targetEntity="Discount", cascade={"persist"})
     * @ORM\JoinTable(name="plugin_paywall_order_item_discount",
     *      joinColumns={
     *          @ORM\JoinColumn(name="order_item_id", referencedColumnName="Id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="discount_id", referencedColumnName="id")
     *      }
     *  )
     *
     * @var Discount
     */
    protected $discounts;

    /**
     * @ORM\OneToMany(targetEntity="Prolongation", mappedBy="orderItem", orphanRemoval=true, cascade={"all"})
     *
     * @var ArrayCollection
     */
    protected $prolongations;

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
        $this->modifications = new ArrayCollection();
        $this->discounts = new ArrayCollection();
        $this->prolongations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Set subscription.
     *
     * @param Newscoop\PaywallBundle\Entity\Subscriptions $subscription
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get subscription.
     *
     * @return Newscoop\PaywallBundle\Entity\Subscription_specification
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set user.
     *
     * @param Newscoop\Entity\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return Newscoop\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set publication.
     *
     * @param Newscoop\Entity\Publication $publication
     *
     * @return Newscoop\Entity\Subscription
     */
    public function setPublication(Publication $publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication.
     *
     * @return Newscoop\Entity\Publication
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Get publication name.
     *
     * @return string
     */
    public function getPublicationName()
    {
        return $this->publication->getName();
    }

    /**
     * Get publication id.
     *
     * @return int
     */
    public function getPublicationId()
    {
        return $this->publication->getId();
    }

    /**
     * Set to pay.
     *
     * @param float $toPay
     *
     * @return Newscoop\Entity\Subscription
     */
    public function setToPay($toPay)
    {
        $this->toPay = (float) $toPay;

        return $this;
    }

    /**
     * Get to pay.
     *
     * @return float
     */
    public function getToPay()
    {
        return (float) $this->toPay;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Newscoop\Entity\Subscription
     */
    public function setType($type)
    {
        $this->type = strtoupper($type) === self::TYPE_TRIAL ? self::TYPE_TRIAL : self::TYPE_PAID;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Test if is trial.
     *
     * @return bool
     */
    public function isTrial()
    {
        return $this->type === self::TYPE_TRIAL;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return Newscoop\Entity\Subscription
     */
    public function setActive($active)
    {
        $this->active = 'N';
        if ($active) {
            $this->active = 'Y';
        }

        return $this;
    }

    /**
     * Is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return strtoupper($this->active) === 'Y';
    }

    /**
     * Get currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency.
     *
     * @return string
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get trial.
     *
     * @return Trial
     */
    public function getTrial()
    {
        return $this->trial;
    }

    /**
     * Set trial.
     *
     * @return Trial
     */
    public function setTrial($trial)
    {
        $this->trial = $trial;

        return $this;
    }

    /**
     * Get create date.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set create date.
     *
     * @param datetime $created_at
     *
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get expire date.
     *
     * @return datetime
     */
    public function getExpireAt()
    {
        return $this->expire_at;
    }

    /**
     * Set expire date.
     *
     * @param datetime $expire_at
     *
     * @return datetime
     */
    public function setExpireAt(\DateTime $expire_at = null)
    {
        $this->expire_at = $expire_at;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set status.
     *
     * @param bool $is_active
     *
     * @return bool
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Gets the Custom field.
     *
     * @return bool
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Sets the Custom field.
     *
     * @param bool $custom the custom
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
     * @return bool
     */
    public function getCustomOther()
    {
        return $this->customOther;
    }

    /**
     * Sets the Second custom field.
     *
     * @param bool $customOther the custom other
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

    /**
     * Gets the value of discount.
     *
     * @return array
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Sets the value of discount.
     *
     * @param array $discount the discount
     *
     * @return self
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Gets the value of duration.
     *
     * @return array
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Sets the value of duration.
     *
     * @param array $duration the duration
     *
     * @return self
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Gets the value of order.
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the value of order.
     *
     * @param Order $order the order
     *
     * @return self
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Gets the value of discountTotal.
     *
     * @return int
     */
    public function getDiscountTotal()
    {
        return $this->discountTotal;
    }

    /**
     * Sets the value of discountTotal.
     *
     * @param int $discountTotal the discounts total
     *
     * @return self
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discountTotal = $discountTotal;

        return $this;
    }

    /**
     * Gets the value of modifications.
     * Can filter modifications by type.
     *
     * @return ArrayCollection
     */
    public function getModifications($type = null)
    {
        if (null === $type) {
            return $this->modifications;
        }

        return $this->modifications->filter(function (Modification $modification) use ($type) {
            return $type === $modification->getLabel();
        });
    }

    /**
     * Sets the value of modifications.
     *
     * @param ArrayCollection $modifications the modifications
     *
     * @return self
     */
    public function setModifications(ArrayCollection $modifications)
    {
        $this->modifications = $modifications;

        return $this;
    }

    public function addModification(Modification $modification)
    {
        if (!$this->hasModification($modification)) {
            $modification->setOrderItem($this);
            $this->modifications->add($modification);
        }

        return $this;
    }

    public function hasModification(Modification $modification)
    {
        return $this->modifications->contains($modification);
    }

    public function removeModification(Modification $modification)
    {
        if ($this->hasModification($modification)) {
            $modification->setOrderItem(null);
            $this->modifications->removeElement($modification);
        }

        return $this;
    }

    public function addDiscount($discount)
    {
        if (!$this->hasDiscount($discount)) {
            $this->discounts->add($discount);
        }

        return $this;
    }

    public function hasDiscount($discount)
    {
        return $this->discounts->contains($discount);
    }

    public function removeDiscount($discount)
    {
        if ($this->hasDiscount($discount)) {
            $this->discounts->removeElement($discount);
        }

        return $this;
    }

    /**
     * Gets the discounts.
     *
     * @return Discount
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Sets the discounts.
     *
     * @param ArrayCollection $discounts the discounts
     *
     * @return self
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    public function calculateToPay()
    {
        $this->calculateModificationsAndToPay();

        return $this;
    }

    public function calculateModificationsAndToPay()
    {
        $this->discountTotal = 0;
        $temp = 0;
        //$this->toPay = $this->subscription->getPrice();
        $totalWithoutDiscount = (float) $this->toPay * $this->duration['value'];

        foreach ($this->modifications as $modification) {
            $temp = $this->toPay + $modification->getAmount();
        }

        foreach ($this->discounts as $discount) {
            if ($discount->getCountBased() && $this->duration['value'] > 1) {
                $temp -= $temp * $discount->getValue();
            }
        }

        $this->toPay = $totalWithoutDiscount;
        if ($temp !== 0) {
            $this->discountTotal = $totalWithoutDiscount - (round($temp, 2) * $this->duration['value']);
        }

        if ($this->toPay < 0) {
            $this->toPay = 0;
        }

        return $this;
    }

    public function addProlongation($prolongation)
    {
        if (!$this->hasProlongation($prolongation)) {
            $this->prolongations->add($prolongation);
        }

        return $this;
    }

    public function hasProlongation($prolongation)
    {
        return $this->prolongations->contains($prolongation);
    }

    /**
     * Gets the value of prolongations.
     *
     * @return ArrayCollection
     */
    public function getProlongations()
    {
        return $this->prolongations;
    }

    /**
     * Sets the value of prolongations.
     *
     * @param ArrayCollection $prolongations the prolongations
     *
     * @return self
     */
    public function setProlongations(ArrayCollection $prolongations)
    {
        $this->prolongations = $prolongations;

        return $this;
    }

    /**
     * Gets the Is prolonged?.
     *
     * @return bool
     */
    public function getProlonged()
    {
        return $this->prolonged;
    }

    /**
     * Sets the Is prolonged?.
     *
     * @param bool $prolonged the prolonged
     *
     * @return self
     */
    public function setProlonged($prolonged)
    {
        $this->prolonged = $prolonged;

        return $this;
    }
}
