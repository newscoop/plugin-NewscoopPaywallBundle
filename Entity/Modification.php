<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modification entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_modification")
 */
class Modification
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
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="modifications")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     *
     * @var Order
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="UserSubscription", inversedBy="modifications")
     * @ORM\JoinColumn(name="order_item_id", referencedColumnName="Id", nullable=true)
     *
     * @var UserSubscription
     */
    protected $orderItem;

    /**
     * @ORM\Column(type="string", name="label")
     *
     * @var string
     */
    protected $label;

    /**
     * @ORM\Column(type="string", name="modification_origin_id")
     *
     * @var string
     */
    protected $modificationOriginId;

    /**
     * @ORM\Column(type="string", name="modification_type")
     *
     * @var string
     */
    protected $modificationType;

    /**
     * @ORM\Column(type="string", name="description", nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", name="amount")
     *
     * @var int
     */
    protected $amount = 0;

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
    protected function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
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
     * Gets the value of orderItem.
     *
     * @return UserSubscription
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Sets the value of orderItem.
     *
     * @param UserSubscription $orderItem the order item
     *
     * @return self
     */
    public function setOrderItem(UserSubscription $orderItem)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * Gets the value of label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the value of label.
     *
     * @param string $label the label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the value of amount.
     *
     * @param int $amount the amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     * Gets the value of modificationType.
     *
     * @return string
     */
    public function getModificationType()
    {
        return $this->modificationType;
    }

    /**
     * Sets the value of modificationType.
     *
     * @param string $modificationType the modification type
     *
     * @return self
     */
    public function setModificationType($modificationType)
    {
        $this->modificationType = $modificationType;

        return $this;
    }

    /**
     * Gets the value of modificationOriginId.
     *
     * @return string
     */
    public function getModificationOriginId()
    {
        return $this->modificationOriginId;
    }

    /**
     * Sets the value of modificationOriginId.
     *
     * @param string $modificationOriginId the modification origin id
     *
     * @return self
     */
    public function setModificationOriginId($modificationOriginId)
    {
        $this->modificationOriginId = $modificationOriginId;

        return $this;
    }
}
