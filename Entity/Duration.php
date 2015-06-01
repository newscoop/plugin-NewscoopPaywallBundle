<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription duration entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_subscription_duration")
 */
class Duration
{
    const MONTHS = 'month';
    const DAYS = 'day';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Subscriptions", inversedBy="ranges")
     * @ORM\JoinColumn(name="subscription_id", referencedColumnName="id")
     *
     * @var Subscriptions
     */
    protected $subscription;

    /**
     * @ORM\Column(type="integer", name="value")
     *
     * @var int
     */
    protected $value;

    /**
     * @ORM\Column(type="string", name="attribute", length=10)
     *
     * @var string
     */
    protected $attribute = self::MONTHS;

    /**
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="durations")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     *
     * @var Discount
     */
    protected $discount;

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of subscription.
     *
     * @return Subscriptions
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Sets the value of subscription.
     *
     * @param Subscriptions $subscription the subscription
     *
     * @return self
     */
    public function setSubscription(Subscriptions $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Gets the value of value.
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of value.
     *
     * @param int $value the value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the value of attribute.
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Sets the value of attribute.
     *
     * @param string $attribute the attribute
     *
     * @return self
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Gets the value of discount.
     *
     * @return Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Sets the value of discount.
     *
     * @param Discount $discount the discount
     *
     * @return self
     */
    public function setDiscount(Discount $discount)
    {
        $this->discount = $discount;

        return $this;
    }
}
