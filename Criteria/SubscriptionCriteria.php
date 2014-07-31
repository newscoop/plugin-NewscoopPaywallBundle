<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Criteria;

use Newscoop\Criteria;

/**
 * Available criteria for subscriptions listing.
 */
class SubscriptionCriteria extends Criteria
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $created;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $query;
}
