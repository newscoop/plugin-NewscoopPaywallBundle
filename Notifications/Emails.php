<?php

/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Notifications;

/**
 * Emails definitions
 */
class Emails
{
    const SUBSCRIPTION_CONFIRMATION = 'subscription_confirmation';
    const USER_CONFIRMATION  = 'user_confirmation';
    const SUBSCRIPTION_STATUS = 'subscription_status_change_confirmation';
    const SUBSCRIPTION_EXPIRATION = 'subscription_expiration';
}
