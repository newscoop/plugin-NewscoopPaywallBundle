<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Calculator;

use Newscoop\PaywallBundle\Order\OrderInterface;

/**
 * Standard pricing calculator.
 */
class DiscountCalculator implements CalculatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function calculate(OrderInterface $order)
    {
        $total = 0;
        foreach ($order->getItems() as $key => $item) {
            $duration = $item->getDuration();
            $total += $duration['value'] * $item->getToPay();
        }

        return (int) round($total);
    }
}
