<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Discount;

/**
 * Discountable interface.
 */
interface DiscountableInterface
{
    /**
     * Gets the total value of discounts.
     *
     * @return int
     */
    public function getDiscountTotal();

    /**
     * Sets the total value of discounts.
     *
     * @param int $discountTotal the discounts total
     */
    public function setDiscountTotal($discountTotal);

    /**
     * Gets the selected discount.
     *
     * @return array
     */
    public function getDiscount();
}
