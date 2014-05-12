<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\Translator;

class ConfigureMenuListener
{   
    private $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {   
        $menu = $event->getMenu();
        $labelPlugins = $this->translator->trans('Plugins');
        $labelPluginName = $this->translator->trans('paywall.title');

        $menu[$labelPlugins]->addChild(
        	$labelPluginName, 
        	array('uri' => $event->getRouter()->generate('newscoop_paywall_admin_admin'))
        );
        
        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('Manage subscriptions'), 
            array('uri' => $event->getRouter()->generate('newscoop_paywall_managesubscriptions_manage')
        ));
        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('Manage subscriptions')]->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('Manage User Subscriptions'), 
            array('uri' => $event->getRouter()->generate('newscoop_paywall_userssubscriptions_index')
        ));
        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('Manage User Subscriptions')]->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('Configure Paywall'), 
            array('uri' => $event->getRouter()->generate('newscoop_paywall_configurepaywall_index')
        ));
        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('Configure Paywall')]->setDisplay(false);
    }
}