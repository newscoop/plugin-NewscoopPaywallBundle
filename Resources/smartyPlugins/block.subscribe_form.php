<?php

/**
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Newscoop subscribe_form block plugin.
 *
 * Type:     block
 * Name:     subscribe_form
 * Purpose:  Displays a subscribe form
 *
 * @param string
 *     $p_params
 * @param string
 *     $p_smarty
 * @param string
 *     $p_content
 *
 * @return string
 */
function smarty_block_subscribe_form($p_params, $p_content, &$smarty)
{
    if (!isset($p_content)) {
        return '';
    }

    $smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $context = $smarty->getTemplateVars('gimme');
    $entityManager = \Zend_Registry::get('container')->getService('em');
    $orderService = \Zend_Registry::get('container')->getService('newscoop_paywall.services.order');
    $url = $context->url;

    if (!isset($p_params['submit_button'])) {
        $p_params['submit_button'] = 'Subscribe';
    }

    if (!isset($p_params['html_code']) || empty($p_params['html_code'])) {
        $p_params['html_code'] = '';
    }

    if (!isset($p_params['button_html_code']) || empty($p_params['button_html_code'])) {
        $p_params['button_html_code'] = '';
    }

    if (!isset($p_params['choose_text']) || empty($p_params['choose_text'])) {
        $p_params['choose_text'] = 'Choose...';
    }

    if (isset($p_params['payment']) && $p_params['payment'] === 'offline') {
        $p_params['payment'] = '/'.$p_params['payment'];
    }

    $meta = array();
    $meta['publication'] = $context->publication->identifier;
    $meta['issue'] = $context->issue->number;
    $meta['section'] = $context->section->number;
    $meta['article'] = $context->article->number;

    $subscriptions = $entityManager->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
        ->findActiveBy($context->language->code, $meta);

    $html = '<form name="subscribe_content" action="'.$url->base.'/paywall/purchase/methods'.$p_params['payment'].'" method="get" '.$p_params['html_code'].'>'."\n";

    $options = '';
    if (array_key_exists('option_text', $p_params)) {
        $optionText = smarty_function_escape_special_chars($p_params['option_text']);
    } else {
        $optionText = 'This %type% for %range% month(s) - %price% %currency%';
    }

    if (array_key_exists('type', $p_params) && $p_params['type'] === 'radio') {
        foreach ($subscriptions as $subscription) {
            foreach ($subscription['ranges'] as $range) {
                $order = $orderService->processAndCalculateOrderItems(array($subscription['id'] => $range['id']), $subscription['currency']);
                $html .= '<p><input type="radio" name="batchorder['.$subscription['id'].']" value="'.$range['id'].'"><span>'.str_replace('%currency%', $subscription['currency'],
                        str_replace('%price%', $order->getTotal(),
                            str_replace('%name%', $subscription['name'],
                            str_replace('%range%', $range['value'],
                                str_replace('%type%', $subscription['type'], $optionText)
                )))).'</span></p>';
            }
        }
    } else {
        foreach ($subscriptions as $subscription) {
            $options = '';
            foreach ($subscription['ranges'] as $range) {
                $order = $orderService->processAndCalculateOrderItems(array($subscription['id'] => $range['id']), $subscription['currency']);
                $options .= '<option value="'.$range['id'].'">'.str_replace('%currency%', $subscription['currency'],
                        str_replace('%price%', $order->getTotal(),
                            str_replace('%name%', $subscription['name'],
                            str_replace('%range%', $range['value'],
                                str_replace('%type%', $subscription['type'], $optionText)
                    )))).'</option>'."\n";
            }

            if ($options !== '') {
                $html  .= '<select name="batchorder['.$subscription['id'].']"><option value="">'.$p_params['choose_text'].'</option>'.$options.'</select><br>';
            }
        }
    }

    $html .= $p_content;

    $html .= '<input type="submit" '
    .'id="subscribe_content_submit" value="'
    .smarty_function_escape_special_chars($p_params['submit_button'])
    .'" '.$p_params['button_html_code']." />\n";
    $html .= "</form>\n";

    return $html;
}
