<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

/**
 * Discount interface.
 */
interface DiscountInterface
{
    /**
     * Gets discount type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets discount type.
     *
     * @param string$type
     */
    public function setType($type);

    /**
     * Gets the discount value.
     *
     * @return decimal
     */
    public function getValue();

    /**
     * Sets the value of value.
     *
     * @param decimal $value
     */
    public function setValue($value);
}
