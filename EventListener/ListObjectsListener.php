<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\CollectObjectsDataEvent;

class ListObjectsListener
{
    /**
     * Register plugin list objects in Newscoop
     *
     * @param CollectObjectsDataEvent $event
     */
    public function registerObjects(CollectObjectsDataEvent $event)
    {
        $event->registerObjectTypes('subscriptions', array(
            'class' => '\Newscoop\PaywallBundle\Meta\MetaSubscriptions',
        ));

        $event->registerObjectTypes('subscription', array(
            'class' => '\Newscoop\PaywallBundle\Meta\MetaMainSubscription',
        ));

        $event->registerListObject('newscoop\paywallbundle\templatelist\price', array(
            'class' => 'Newscoop\PaywallBundle\TemplateList\Price',
            'list' => 'pricetable',
            'url_id' => 'pslid',
        ));
    }
}
