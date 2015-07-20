<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Entity\OrderInterface;

/**
 * Discount processor interface.
 */
interface DiscountProcessorInterface
{
    /**
     * Processes discounts for given order.
     *
     * It applies discounts for each order item,
     * depends which discount type has been chosen.
     *
     * @param DiscountableInterface $object
     *
     * @return OrderInterface
     */
    public function process(DiscountableInterface $object);
}
