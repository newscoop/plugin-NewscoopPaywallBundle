<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Display all available subscriptions with its price.
 *
 * Type:     block
 * Name:     price_table
 * Purpose:  Display price table on frontend
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
function smarty_block_list_pricetable($params, $content, &$smarty, &$repeat)
{
    $context = $smarty->getTemplateVars('gimme');

    if (!isset($content)) { // init
        $start = $context->next_list_start('Newscoop\PaywallBundle\TemplateList\PriceList');
        $list = new \Newscoop\PaywallBundle\TemplateList\PriceList(new \Newscoop\PaywallBundle\Criteria\SubscriptionCriteria());
        $list->getList($start, $params);
        if ($list->isEmpty()) {
            $context->setCurrentList($list, array());
            $context->resetCurrentList();
            $repeat = false;

            return;
        }

        $context->setCurrentList($list, array('subscription'));
        $context->subscription = $context->current_pricetable_list->current;
        $repeat = true;
    } else { // next
        $context->current_pricetable_list->defaultIterator()->next();
        if (!is_null($context->current_pricetable_list->current)) {
            $context->subscription = $context->current_pricetable_list->current;
            $repeat = true;
        } else {
            $context->resetCurrentList();
            $repeat = false;
        }
    }

    return $content;
}
