<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[getGS('Plugins')]->addChild(
        	getGS('Newscoop Paywall'), 
        	array('uri' => $event->getRouter()->generate('newscoop_paywall_admin_admin'))
        );
        
        $menu[getGS('Plugins')][getGS('Newscoop Paywall')]->addChild(
            getGS('Manage subscriptions'), 
            array('uri' => $event->getRouter()->generate('newscoop_paywall_managesubscriptions_manage')
        ));
        $menu[getGS('Plugins')][getGS('Newscoop Paywall')][getGS('Manage subscriptions')]->setDisplay(false);
    }
}