<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Order\OrderInterface;

/**
 * Abstract discount.
 */
class Discount implements DiscountInterface
{
    protected $order;

    /**
     * {@inheritdoc}
     */
    public function applyTo(OrderInterface $order)
    {
        foreach ($order->getItems() as $key => $item) {
            if ($this->isEligibleForGlobalDiscount($order)) {
                $this->addGlobalDiscount($item);
            }
        }

        $this->order = $order;
    }

    protected function isEligibleForGlobalDiscount($order)
    {
        if ($order->countItems() > 1 /* && $systemPref->PaywallGlobalDiscount > 0**/) {
            return true;
        }

        return false;
    }

    protected function isEligibleForDiscount($item)
    {
        if ($item->getDiscount()) {
            return true;
        }

        return false;
    }

    protected function addGlobalDiscount($item)
    {
        $globalDiscount = 5 / 100; // get from system pref
        $item->setToPay($item->getToPay() - ($item->getToPay() * $globalDiscount));

        return $item;
    }
}
