<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prolongation entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_prolongation")
 */
class Prolongation implements ProlongationInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", name="total")
     *
     * @var int
     */
    protected $total = 0;

    /**
     * @ORM\Column(type="boolean", name="is_approved")
     *
     * @var bool
     */
    protected $approved = false;

    /**
     * @ORM\ManyToOne(targetEntity="UserSubscription", inversedBy="prolongations")
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="Id", nullable=true)
     *
     * @var ProlongableInterface
     */
    protected $orderItem;

    /**
     * @ORM\Column(type="array", name="period")
     *
     * @var array
     */
    protected $period;

    /**
     * @ORM\Column(type="string", name="currency", length=3)
     *
     * @var string
     */
    protected $currency;

    /**
     * @ORM\Column(type="integer", name="discount_total")
     *
     * @var int
     */
    protected $discountTotal = 0;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
     * Gets the value of total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Sets the value of total.
     *
     * @param int $total the total
     *
     * @return self
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Gets the value of approved.
     *
     * @return bool
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Sets the value of approved.
     *
     * @param bool $approved the approved
     *
     * @return self
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Gets the value of orderItem.
     *
     * @return ProlongableInterface
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Sets the value of orderItem.
     *
     * @param ProlongableInterface $orderItem the order item
     *
     * @return self
     */
    public function setOrderItem(ProlongableItemInterface $orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * Gets the value of period.
     *
     * @return array
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Sets the value of period.
     *
     * @param array $period the period
     *
     * @return self
     */
    public function setPeriod(array $period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Gets the value of currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the value of currency.
     *
     * @param string $currency the currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

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
     * @param int $discountTotal the discount total
     *
     * @return self
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discountTotal = $discountTotal;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param \DateTime $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the value of createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
