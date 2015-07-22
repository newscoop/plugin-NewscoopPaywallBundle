<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity;

/**
 * Priceable interface.
 */
interface PriceableInterface
{
    /**
     * Gets price.
     *
     * @return int
     */
    public function getPrice();

    /**
     * Sets price.
     *
     * @param int $price
     */
    public function setPrice($price);

    /**
     * Gets currency.
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Sets currency.
     *
     * @param string $currency
     */
    public function setCurrency($currency);
}
