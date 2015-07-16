<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\TemplateList;

use Newscoop\TemplateList\BaseList;
use Newscoop\PaywallBundle\Meta\MetaSubscription;
use Doctrine\Common\Collections\Criteria;

/**
 * User Subscriptions List.
 */
class UserSubscriptionsList extends BaseList
{
    protected function prepareList($criteria, $parameters)
    {
        $service = \Zend_Registry::get('container')->get('paywall.subscription.service');
        $userService = \Zend_Registry::get('container')->get('user');
        $user = $userService->getCurrentUser();
        $criteria->user = $user->getId();
        $list = $service->getMySubscriptionsByCriteria($criteria);
        $filteredIitems = $service->filterMySubscriptions($list->items);

        $list->items = array();
        foreach ($filteredIitems as $key => $value) {
            $list->items[] = new MetaSubscription($value);
        }

        return $list;
    }

    protected function convertParameters($firstResult, $parameters)
    {
        $this->criteria->orderBy = array();
        // run default simple parameters converting
        parent::convertParameters($firstResult, $parameters);

        if (array_key_exists('locale', $parameters)) {
            $this->criteria->locale = $parameters['locale'];
        }
    }
}
