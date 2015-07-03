<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Function to check if user has access to content.
 *
 * @param array  $params
 * @param object $smarty
 *
 * @return string
 */
function smarty_modifier_has_subscription($user, $smarty)
{
    $context = \CampTemplate::singleton()->context();
    $user = $context->user;
    if (!$user) {
        return false;
    }

    $em = \Zend_Registry::get('container')->getService('em');
    $userSubscriptions = $em->getRepository("Newscoop\PaywallBundle\Entity\UserSubscription")
        ->getValidSubscriptionsBy($user->identifier)->getArrayResult();

    $publication = $context->publication;
    $issue = $context->issue;
    $section = $context->section;
    $article = $context->article;

    try {
        foreach ($userSubscriptions as $key => $value) {
            $specification = $value['subscription']['specification'][0];
            if ($value['subscription']['type'] === 'publication' && $publication) {
                if ($specification['publication'] == $publication->identifier) {
                    return true;
                }
            }

            if ($value['subscription']['type'] === 'issue' && $issue) {
                if ($specification['issue'] == $issue->number &&
                    $specification['publication'] == $issue->publication->identifier) {
                    return true;
                }
            }

            if ($value['subscription']['type'] === 'section' && $section) {
                if ($specification['section'] == $section->number &&
                    $specification['issue'] == $section->issue->number &&
                    $specification['publication'] == $issue->publication->identifier) {
                    return true;
                }
            }

            if ($value['subscription']['type'] === 'article' && $article) {
                if ($specification['article'] == $article->number &&
                    $specification['publication'] == $article->publication->identifier &&
                    $specification['issue'] == $article->issue->number &&
                    $specification['section'] == $article->section->number) {
                    return true;
                }
            }
        }
    } catch (\Exception $e) {
        return false;
    }

    return false;
}
