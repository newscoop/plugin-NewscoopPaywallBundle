<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Order;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Order.
 */
class Order implements OrderInterface
{
    /**
     * Items in order.
     *
     * @var Collection
     */
    protected $items;

    /**
     * User.
     *
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
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
    public function setUser(\Newscoop\Entity\User $user)
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

        /*foreach ($this->items as $existingItem) {
            if ($item->equals($existingItem)) {
                $existingItem->merge($item, false);

                return $this;
            }
        }*/

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
}
