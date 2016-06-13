<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Newscoop\Entity\User;

/**
 * Order entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\OrderRepository")
 * @ORM\Table(name="plugin_paywall_order")
 */
class Order implements OrderInterface
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
     * @ORM\OneToMany(targetEntity="UserSubscription", mappedBy="order", orphanRemoval=true, cascade={"all"})
     *
     * @var Collection
     */
    protected $items;

    /**
     * @ORM\Column(type="string", name="currency", length=3)
     *
     * @var string
     */
    protected $currency;

    /**
     * @ORM\Column(type="integer", name="total")
     *
     * @var int
     */
    protected $total = 0;

    /**
     * @ORM\Column(type="integer", name="items_total")
     *
     * @var int
     */
    protected $itemsTotal = 0;

    /**
     * @ORM\Column(type="integer", name="discount_total")
     *
     * @var int
     */
    protected $discountTotal = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="Id")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Modification", mappedBy="order", orphanRemoval=true, cascade={"all"})
     *
     * @var Collection
     */
    protected $modifications;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="order", orphanRemoval=true, cascade={"all"})
     *
     * @var Collection
     */
    protected $payments;

    /**
     * @ORM\ManyToMany(targetEntity="Discount", cascade={"persist"})
     * @ORM\JoinTable(name="plugin_paywall_order_discount",
     *      joinColumns={
     *          @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="discount_id", referencedColumnName="id")
     *      }
     *  )
     *
     * @var Collection
     */
    protected $discounts;

    /**
     * @ORM\Column(type="string", name="payment_state")
     *
     * @var string
     */
    protected $paymentState = PaymentInterface::STATE_NEW;

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
        $this->items = new ArrayCollection();
        $this->modifications = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->discounts = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function countItems()
    {
        return $this->items->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem($item)
    {
        if ($this->hasItem($item)) {
            return $this;
        }

        $this->items->add($item);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem($item)
    {
        if ($this->hasItem($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($item)
    {
        return $this->items->contains($item);
    }

    /**
     * {@inheritdoc}
     */
    public function getToPay()
    {
        return $this->getTotal();
    }

    /**
     * {@inheritdoc}
     */
    public function setToPay($total)
    {
        $this->setTotal($total);

        return $this;
    }

    /**
     * Gets the value of total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total / 100;
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
        $this->total = $total * 100;

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
     * {@inheritdoc}
     */
    public function getDiscountTotal()
    {
        return $this->discountTotal / 100;
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discountTotal = $discountTotal * 100;

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
     * @return Collection
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Sets the discounts.
     *
     * @param Collection $discounts the discounts
     *
     * @return self
     */
    public function setDiscounts(Collection $discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    /**
     * Calculates total order price including
     * all discounts.
     *
     * @return self
     */
    public function calculateTotal()
    {
        $this->calculateItemsTotal();
        $this->total = ($this->itemsTotal / 100 + $this->discountTotal / 100) * 100;
        if ($this->total < 0) {
            $this->total = 0;
        }

        return $this;
    }

    /**
     * Calculates all items discounts and unit prices.
     *
     * @return self
     */
    public function calculateItemsTotal()
    {
        $itemsTotal = 0;
        $this->discountTotal = 0;
        foreach ($this->items as $item) {
            $item->calculateToPay();
            $itemsTotal += $item->getToPay();

            $this->discountTotal -= $item->getDiscountTotal() * 100;
        }

        $this->itemsTotal = round($itemsTotal, 2) * 100;

        return $this;
    }

    /**
     * Gets the value of itemsTotal.
     *
     * @return int
     */
    public function getItemsTotal()
    {
        return $this->itemsTotal / 100;
    }

    /**
     * Sets the value of itemsTotal.
     *
     * @param int $itemsTotal the items total
     *
     * @return self
     */
    public function setItemsTotal($itemsTotal)
    {
        $this->itemsTotal = $itemsTotal * 100;

        return $this;
    }

    /**
     * Gets the payments.
     *
     * @return Payment
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function getPaymentState()
    {
        return $this->paymentState;
    }

    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;
    }

    public function addPayment(PaymentInterface $payment)
    {
        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
            $this->setPaymentState($payment->getState());
        }
    }

    public function removePayment(PaymentInterface $payment)
    {
        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setOrder(null);
        }
    }

    public function hasPayment(PaymentInterface $payment)
    {
        return $this->payments->contains($payment);
    }

    public function getOrder()
    {
        return $this;
    }
}
