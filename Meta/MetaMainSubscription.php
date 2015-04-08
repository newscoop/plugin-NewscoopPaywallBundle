<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Meta;

/**
 * Meta main subscription class
 */
class MetaMainSubscription
{
    public $name;
    public $price;
    public $range;
    public $type;
    public $currency;
    public $description;

    /**
     * Construct
     *
     * @param array $subscription
     */
    public function __construct(array $subscription)
    {
        $this->name = $subscription['name'];
        $this->price = $subscription['price'];
        $this->range = $subscription['range'];
        $this->type = $subscription['type'];
        $this->currency = $subscription['currency'];
        $this->description = $subscription['description'];
    }
}
