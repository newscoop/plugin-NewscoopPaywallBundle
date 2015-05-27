<?php

/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\TemplateList;

use Newscoop\TemplateList\BaseList;
use Newscoop\PaywallBundle\Meta\MetaSubscription;

/**
 * User Subscriptions List
 */
class UserSubscriptionsList extends BaseList
{

    protected function prepareList($criteria, $parameters)
    {
        $service = \Zend_Registry::get('container')->get('paywall.subscription.service');
        $userService = \Zend_Registry::get('container')->get('user');
        $user = $userService->getCurrentUser();
        if (!$user) {
            return array();
        }

        $criteria->user = $user->getId();
        $list = $service->getUserSubscriptionsByCriteria($criteria);
        foreach ($list->items as $key => $value) {
            $list->items[$key] = new MetaSubscription($value['id']);
        }

        return $list;
    }

    protected function convertParameters($firstResult, $parameters)
    {
        $this->criteria->orderBy = array();
        // run default simple parameters converting
        parent::convertParameters($firstResult, $parameters);
    }
}
