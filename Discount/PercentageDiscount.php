<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Order\OrderInterface;

/**
 * Percentage discount.
 */
class PercentageDiscount extends Discount
{
    /**
     * {@inheritdoc}
     */
    public function applyTo(OrderInterface $order)
    {
        parent::applyTo($order);

        foreach ($this->order->getItems() as $key => $item) {
            if ($this->isEligibleForDiscount($item)) {
                $discount = $item->getDiscount();
                $discountPrice = (float) $item->getToPay() * $discount['value'];
                $item->setToPay($item->getToPay() - $discountPrice);
            }
        }

        return $this->order;
    }
}
