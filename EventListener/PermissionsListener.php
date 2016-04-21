<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2016 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginPermissionsEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Newscoop\PaywallBundle\Permissions;

class PermissionsListener
{
    /**
      * TranslatorInterface.
      *
      * @var Translator
      */
     protected $translator;

     /**
      * Construct.
      *
      * @param TranslatorInterface $translator Translator object
      */
     public function __construct(TranslatorInterface $translator)
     {
         $this->translator = $translator;
     }

     /**
      * Register plugin permissions in Newscoop ACL.
      *
      * @param PluginPermissionsEvent $event
      */
     public function registerPermissions(PluginPermissionsEvent $event)
     {
         $event->registerPermissions($this->translator->trans('paywall.title'), array(
            Permissions::SUBSCRIPTION_ADD => $this->translator->trans('paywall.permissions.add'),
            Permissions::SUBSCRIPTIONS_MANAGE => $this->translator->trans('paywall.permissions.manage'),
            Permissions::SUBSCRIPTIONS_VIEW => $this->translator->trans('paywall.permissions.list'),
            Permissions::ORDERS_VIEW => $this->translator->trans('paywall.permissions.orders.main'),
            Permissions::ORDERS_MANAGE => $this->translator->trans('paywall.permissions.orders.manage'),
            Permissions::CONFIGURE => $this->translator->trans('paywall.permissions.configure'),
            Permissions::DISCOUNTS_VIEW => $this->translator->trans('paywall.permissions.discounts.main'),
            Permissions::DISCOUNTS_MANAGE => $this->translator->trans('paywall.permissions.discounts.manage'),
            Permissions::CURRENCIES_VIEW => $this->translator->trans('paywall.permissions.currencies.main'),
            Permissions::CURRENCIES_MANAGE => $this->translator->trans('paywall.permissions.currencies.manage'),
            Permissions::PAYMENTS_VIEW => $this->translator->trans('paywall.permissions.payments.main'),
            Permissions::PAYMENTS_MANAGE => $this->translator->trans('paywall.permissions.payments.manage'),
            Permissions::SIDEBAR => $this->translator->trans('paywall.permissions.sidebar'),
         ));
     }
}
