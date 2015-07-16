<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Events;

/**
 * Paywall events definitions.
 */
class PaywallEvents
{
    const ORDER_SUBSCRIPTION = 'paywall.subscription.order';
    const SUBSCRIPTION_STATUS_CHANGE = 'paywall.subscription.status_change';
    const SUBSCRIPTION_EXPIRATION = 'paywall.subscription.expiration';
    const ADMIN_ORDER_SUBSCRIPTION = 'paywall.subscription.admin_order';
}
