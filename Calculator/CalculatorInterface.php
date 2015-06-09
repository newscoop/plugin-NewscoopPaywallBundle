<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Calculator;

use Newscoop\PaywallBundle\Entity\OrderInterface;

/**
 * Price calculator interface.
 */
interface CalculatorInterface
{
    /**
     * Calculate price for the order object.
     *
     * @param OrderInterface $order
     *
     * @return int
     */
    public function calculate(OrderInterface $order);
}
