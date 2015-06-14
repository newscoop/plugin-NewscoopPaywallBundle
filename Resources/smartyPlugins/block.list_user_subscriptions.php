<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Display all available user subscriptions.
 *
 * Type:     block
 * Name:     user_subscriptions
 * Purpose:  Displays user subscriptions
 *
 * @param string
 *     $params
 * @param string
 *     $p_smarty
 * @param string
 *     $content
 *
 * @return
 */
function smarty_block_list_user_subscriptions($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');

    if (!isset($content)) { // init
        $start = $context->next_list_start('Newscoop\PaywallBundle\TemplateList\UserSubscriptionsList');
        $list = new \Newscoop\PaywallBundle\TemplateList\UserSubscriptionsList(
            new \Newscoop\PaywallBundle\Criteria\SubscriptionCriteria()
        );

        $list->getList($start, $params);
        if ($list->isEmpty()) {
            $context->setCurrentList($list, array());
            $context->resetCurrentList();
            $repeat = false;

            return;
        }

        $context->setCurrentList($list, array('user_subscription'));
        $context->user_subscription = $context->current_usersubscriptions_list->current;
        $repeat = true;
    } else { // next
        $context->current_usersubscriptions_list->defaultIterator()->next();
        if (!is_null($context->current_usersubscriptions_list->current)) {
            $context->user_subscription = $context->current_usersubscriptions_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
