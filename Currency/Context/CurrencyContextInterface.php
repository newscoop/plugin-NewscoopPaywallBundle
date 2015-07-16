<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Currency\Context;

/**
 * Currency Context interface.
 */
interface CurrencyContextInterface
{
    const CURRENCY_KEY = '_paywall_plugin_currency';

    /**
     * Gets current currency from the session.
     *
     * @return string Current currency
     */
    public function getCurrency();

    /**
     * Sets current currency in session.
     *
     * @param string $currency currency
     */
    public function setCurrency($currency);
}
