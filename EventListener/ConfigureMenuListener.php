<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Newscoop\NewscoopBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[getGS('Plugins')]->addChild(
        	'Paywall Plugin', 
        	array('uri' => $event->getRouter()->generate('newscoop_exampleplugin_default_admin'))
        );
    }
}