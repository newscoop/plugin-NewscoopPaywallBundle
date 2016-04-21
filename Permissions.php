<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2016 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle;

/**
 * Paywall permissions definition.
 */
class Permissions
{
    const SUBSCRIPTION_ADD = 'plugin_paywall_subscription-add';
    const SUBSCRIPTIONS_MANAGE = 'plugin_paywall_subscriptions-manage';
    const SUBSCRIPTIONS_VIEW = 'plugin_paywall_subscriptions-view';
    const ORDERS_VIEW = 'plugin_paywall_orders-view';
    const ORDERS_MANAGE = 'plugin_paywall_orders-manage';
    const CONFIGURE = 'plugin_paywall_configure';
    const DISCOUNTS_VIEW = 'plugin_paywall_discounts-view';
    const DISCOUNTS_MANAGE = 'plugin_paywall_discounts-manage';
    const CURRENCIES_VIEW = 'plugin_paywall_currencies-view';
    const CURRENCIES_MANAGE = 'plugin_paywall_currencies-manage';
    const PAYMENTS_VIEW = 'plugin_paywall_payments-view';
    const PAYMENTS_MANAGE = 'plugin_paywall_payments-manage';
    const SIDEBAR = 'plugin_paywall_sidebar';
}
