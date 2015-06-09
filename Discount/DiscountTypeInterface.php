<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Entity\DiscountInterface;

/**
 * Discount Type interface.
 */
interface DiscountTypeInterface
{
    /**
     * Applies the discount to the order items.
     *
     * @param DiscountableInterface $object
     * @param DiscountInterface     $discount
     */
    public function applyTo(DiscountableInterface $object, DiscountInterface $discount);
}
