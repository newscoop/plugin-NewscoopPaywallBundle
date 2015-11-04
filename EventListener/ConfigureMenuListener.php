<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\TranslatorInterface;

class ConfigureMenuListener
{
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
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
            array('uri' => $event->getRouter()->generate('newscoop_paywall_managesubscriptions_manage'))
        );

        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('Manage subscriptions')]->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('Configure Paywall'),
            array('uri' => $event->getRouter()->generate('newscoop_paywall_configurepaywall_index'))
        )->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('paywall.menu.label.discounts'),
            array('uri' => $event->getRouter()->generate('newscoop_paywall_discount_index'))
        )->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('paywall.menu.label.currencies'),
            array('uri' => $event->getRouter()->generate('paywall_plugin_currency_index'))
        )->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('paywall.menu.label.orders'),
            array('uri' => $event->getRouter()->generate('paywall_plugin_userorder_index'))
        )->setDisplay(false);
    }
}
