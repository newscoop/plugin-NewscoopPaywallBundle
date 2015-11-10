<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

/**
 * Payment Method Context interface.
 */
interface PaymentMethodInterface
{
    const METHOD_KEY = '_paywall_plugin_payment_method';

    /**
     * Gets current payment method from the session.
     *
     * @return string Current payment method
     */
    public function getMethod();

    /**
     * Sets current payment method in session.
     *
     * @param string|null $method Payment method
     */
    public function setMethod($method = null);
}
