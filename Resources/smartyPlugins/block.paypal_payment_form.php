<?php
/**
 * @package Newscoop
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


/**
 * Newscoop paypal_payment_form block plugin
 *
 * Type:     block
 * Name:     paypal_payment_form
 * Purpose:  Displays a form for paypal cart
 *
 * @param string
 *     $params
 * @param string
 *     $p_smarty
 * @param string
 *     $content
 *
 * @return
 *
 */
function smarty_block_paypal_payment_form($params, $content, &$smarty, &$repeat)
{
    if (!isset($content)) {
        return '';
    }

    $smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $context = $smarty->getTemplateVars('gimme');
    $container = \Zend_Registry::get('container');
    $subscriptionService = $container->getService('subscription.service');
    $subscriptionsConfig = $subscriptionService->getSubscriptionsConfig();
    $url = $context->url;

    $choosenSubscription = $subscriptionService->getOneById($params['subscriptionId']);

    if (!$choosenSubscription) {
        throw new Exception("Subscription don't exists", 1);
    }

    $formData = array(
        'seller_email' => $subscriptionsConfig['paypal_config']['seller_email'],
        'paypal_url' => 'https://www.paypal.com/cgi-bin/webscr',
        'subscription_id' => $choosenSubscription->getId(),
        'subscription_amount' => $choosenSubscription->getToPay(),
        'subscription_currency' => $choosenSubscription->getCurrency(),
        'subscription_item_name' => str_replace('%publication_name%', $context->publication->name, $subscriptionsConfig['paypal_config']['item_name_format']),
        'language_code' => $context->language->code
    );

    if (isset($params['test'])) {
        $formData['paypal_url'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        $formData['seller_email'] = $subscriptionsConfig['paypal_config']['test_seller_email'];
    }

    if (!isset($params['submit_button'])) {
        $params['submit_button'] = 'Buy subscription';
    }

    if (!isset($params['html_code']) || empty($params['html_code'])) {
        $params['html_code'] = '';
    }
    if (!isset($params['button_html_code']) || empty($params['button_html_code'])) {
        $params['button_html_code'] = '';
    }


    $html = '<form name="subscribe_content" action="'.$formData['paypal_url'].'" method="post" '.$params['html_code'].'>'."\n";

    if (isset($template)) {
        $html .= "<input type=\"hidden\" name=\"tpl\" value=\"" . $template->identifier . "\" />\n";
    }

    $html .= '<input type="hidden" name="cmd" value="_xclick">'."\n";
    $html .= '<input type="hidden" name="business" value="'.$formData['seller_email'].'">'."\n";
    $html .= '<input type="hidden" name="item_name" value="'.smarty_function_escape_special_chars($formData['subscription_item_name']).'">'."\n";
    $html .= '<input type="hidden" name="item_number" value="'.$formData['subscription_id'].'">'."\n";
    $html .= '<input type="hidden" name="amount" value="'.$formData['subscription_amount'].'">'."\n";
    $html .= '<input type="hidden" name="display" value="1">'."\n";
    $html .= '<input type="hidden" name="no_shipping" value="1">'."\n";
    $html .= '<input type="hidden" name="no_note" value="1"> '."\n";
    $html .= '<input type="hidden" name="currency_code" value="'.$formData['subscription_currency'].'"> '."\n";
    $html .= '<input type="hidden" name="lc" value="'.$formData['language_code'].'">'."\n";
    $html .= '<input type="hidden" name="return" value="'.$container->get('router')->generate('newscoop_paywall_default_statussuccess', array(), true).'">'."\n";
    $html .= '<input type="hidden" name="cancel_return" value="'.$container->get('router')->generate('newscoop_paywall_default_statuscancel', array(), true).'">'."\n";
    $html .= '<input type="hidden" name="notify_url" value="'.$container->get('router')->generate('newscoop_paywall_default_callback', array(), true).'">'."\n";
    $html .= '<input type="hidden" name="custom" value="'.$choosenSubscription->getId().'__'.$choosenSubscription->getUser()->getId().'">'."\n";

    foreach ($context->url->form_parameters as $param) {
        if ($param['name'] == 'tpl') {
            continue;
        }
        $html .= '<input type="hidden" name="'.$param['name']
        .'" value="'.htmlentities($param['value'])."\" />\n";
    }


    $html .= $content;

    $html .= "<input type=\"submit\" name=\"submit_form\" id=\"paypal_form_submit\" value=\"".smarty_function_escape_special_chars($params['submit_button'])."\" " . $params['button_html_code'] . " />\n";

    $html .= "</form>\n";

    return $html;
}