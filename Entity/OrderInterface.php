<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Newscoop\Entity\User;
use Newscoop\PaywallBundle\Discount\DiscountableInterface;

/**
 * Order interface.
 */
interface OrderInterface extends DiscountableInterface
{
    /**
     * Get order items.
     *
     * @return Collection An array or collection
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param Collection $items
     */
    public function setItems(Collection $items);

    /**
     * Returns number of order items.
     *
     * @return int
     */
    public function countItems();

    /**
     * Adds item to order.
     *
     * @param OrderItemInterface $item
     */
    public function addItem($item);

    /**
     * Remove item from order.
     *
     * @param OrderItemInterface $item
     */
    public function removeItem($item);

    /**
     * Has order item.
     *
     * @param OrderItemInterface $item
     *
     * @return bool
     */
    public function hasItem($item);

    /**
     * Sets User.
     *
     * @param User $user
     */
    public function setUser(User $user);

    /**
     * Gets User.
     *
     * @return User
     */
    public function getUser();
}
