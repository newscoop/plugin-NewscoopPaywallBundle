<?php
/**
 * @package Newscoop
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


/**
 * Newscoop subscribe_form block plugin
 *
 * Type:     block
 * Name:     subscribe_form
 * Purpose:  Displays a form for subscribe button
 *
 * @param string
 *     $p_params
 * @param string
 *     $p_smarty
 * @param string
 *     $p_content
 *
 * @return
 *
 */
function smarty_block_subscribe_form($p_params, $p_content, &$smarty, &$p_repeat)
{
    if (!isset($p_content)) {
        return '';
    }

    $smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $context = $smarty->getTemplateVars('gimme');
    $subscriptionService = \Zend_Registry::get('container')->getService('subscription.service');
    $url = $context->url;

    if (!isset($p_params['submit_button'])) {
        $p_params['submit_button'] = 'Subscribe';
    }
    $anchor = isset($p_params['anchor']) ? '#'.$p_params['anchor'] : null;
    if (!isset($p_params['html_code']) || empty($p_params['html_code'])) {
        $p_params['html_code'] = '';
    }
    if (!isset($p_params['button_html_code']) || empty($p_params['button_html_code'])) {
        $p_params['button_html_code'] = '';
    }

    $meta = array();

    if ($context->publication->identifier) {
        $meta['publication'] = $context->publication->identifier;
    }

    if ($context->issue->number) {
        $meta['issue'] = $context->issue->number;
    }

    if ($context->section->number) {
        $meta['section'] = $context->section->number;
    }

    if ($context->article->number) {
        $meta['article'] = $context->article->number;
    }

    $subscriptionsConfig = $subscriptionService->getSubscriptionsConfig();
    $matched = array();
    $types = array(
        'publication'   => 4,
        'issue'         => 3,
        'section'       => 2,
        'article'       => 1
    );
    asort($types);
    
    // find specific type
    $specificType = false;
    foreach ($subscriptionsConfig['subscriptions'] as $name => $definition ) {
        $specificElement = false;
        if (array_key_exists('specify', $definition)) {
            $parts = true;
            foreach ($definition['specify'] as $contentName => $value ) {
                if (array_key_exists($contentName, $meta) && $meta[$contentName] != $value) {
                    $parts = false;
                }
            }

            if ($parts === true) {
                $specificElement = true;
            }
        }

        if (array_key_exists($definition['type'], $meta)) {
            if (
                !array_key_exists($definition['type'], $matched) || 
                $specificElement === true || 
                (!array_key_exists('specify', $definition) && !$specificType)
            ) {
                $matched[$name] = $definition + array('definition_name' => $name);
                if ($specificElement) {
                    $specificType = $definition['type'];
                }
            }
        }
    }

    $html = '<form name="subscribe_content" action="'.$url->base.'/paywall/subscriptions/get'.$anchor.'" method="post" '.$p_params['html_code'].'>'."\n";

    if (isset($template)) {
        $html .= "<input type=\"hidden\" name=\"tpl\" value=\"" . $template->identifier . "\" />\n";
    }

    foreach ($context->url->form_parameters as $param) {
        if ($param['name'] == 'tpl') {
            continue;
        }
        $html .= '<input type="hidden" name="'.$param['name']
        .'" value="'.htmlentities($param['value'])."\" />\n";
    }

    $options = '';
    if (array_key_exists('option_text', $p_params)) {
        $optionText = smarty_function_escape_special_chars($p_params['option_text']);
    } else {
        $optionText = 'This %type% - %range% for %price% %currency%';
    }

    foreach ($meta as $type => $value) {    
        $html .= '<input type="hidden" name="'.$type.'_id" value="'.$value.'" />'."\n";
    }

    $html .= '<input type="hidden" name="language_id" value="'.$context->language->number.'" />'."\n";

    if (array_key_exists('type', $p_params) && $p_params['type'] == 'radio') {
        foreach ($matched as $type => $definition) {
            $html .= '<p><input type="radio" name="subscription_name" value="'.$definition['definition_name'].'">' .str_replace('%currency%', $definition['currency'],
                    str_replace('%price%', $definition['price'],
                        str_replace('%range%', $definition['range'],
                            str_replace('%type%', $definition['type'], $optionText)
                ))).'</p>';
        }
    } else {
        foreach ($matched as $type => $definition) {
            $options .= '<option value="'.$definition['definition_name'].'">'.str_replace('%currency%', $definition['currency'],
                str_replace('%price%', $definition['price'],
                    str_replace('%range%', $definition['range'],
                        str_replace('%type%', $definition['type'], $optionText)
            ))).'</option>'."\n";
        }
        $html  .= "<select name=\"subscription_name\">".$options."</select>";
    }

    $html .= $p_content;

    $html .= "<input type=\"submit\" name=\"submit_comment\" "
    ."id=\"subscribe_content_submit\" value=\""
    .smarty_function_escape_special_chars($p_params['submit_button'])
    ."\" " . $p_params['button_html_code'] . " />\n";
    $html .= "</form>\n";

    return $html;
}
