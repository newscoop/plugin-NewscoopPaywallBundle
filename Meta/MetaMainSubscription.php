<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Meta;

/**
 * Meta main subscription class.
 */
class MetaMainSubscription
{
    public $identifier;
    public $name;
    public $price;
    public $range;
    public $type;
    public $currency;
    public $description;

    /**
     * Construct.
     *
     * @param array $subscription
     */
    public function __construct(array $subscription)
    {
        $this->identifier = $subscription['id'];
        $this->name = $subscription['name'];
        $this->price = $subscription['price'];
        $this->ranges = $subscription['ranges'];
        $this->type = $subscription['type'];
        $this->currency = $subscription['currency'];
        $this->description = $subscription['description'];
    }
}
