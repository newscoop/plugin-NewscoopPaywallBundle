<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\TemplateList;

use Newscoop\TemplateList\BaseList;
use Newscoop\PaywallBundle\Meta\MetaMainSubscription;

/**
 * Price List.
 */
class PriceList extends BaseList
{
    protected function prepareList($criteria, $parameters)
    {
        $service = \Zend_Registry::get('container')->get('paywall.subscription.service');
        $list = $service->getSubscriptionsByCriteria($criteria);
        $list->items = $list->items->getArrayResult();
        foreach ($list->items as $key => $value) {
            $list->items[$key] = new MetaMainSubscription($value);
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
