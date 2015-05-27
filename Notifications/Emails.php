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
    const ADMIN_CREATED_CONFIRMATION = 'admin_created_subscription_confirmation';

    /* we distinquish two levels of notifications
    	NOTIFY_LEVEL_ONE -
    		notify will be send x (e.g. 7) days before expiration
    	NOTIFY_LEVEL_TWO -
    		notify will be send x (e.g. 3)days before expiration
    */
    const NOTIFY_LEVEL_ONE = 1;
    const NOTIFY_LEVEL_TWO = 2;
}
