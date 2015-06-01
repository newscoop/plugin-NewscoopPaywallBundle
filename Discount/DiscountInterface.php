<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Order\OrderInterface;

/**
 * Discount interface.
 */
interface DiscountInterface
{
    /**
     * Applies the discount to the order items.
     *
     * @param OrderInterface $subject
     *
     * @return mixed
     */
    public function applyTo(OrderInterface $order);
}
