<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Order\OrderInterface;

/**
 * Process all discounts for order items.
 */
class DiscountProcessor implements DiscountProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(OrderInterface $order, DiscountInterface $discount)
    {
        return $discount->applyTo($order);
    }
}
