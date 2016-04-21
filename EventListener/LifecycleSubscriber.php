<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\PaywallBundle\Entity\Gateway;

/**
 * Event lifecycle management.
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'newscoop/newscoop-paywall-bundle';

    private $em;
    private $dispatcher;
    private $scheduler;
    private $systemPreferences;
    private $classDir;
    private $pluginDir = '/../../../../';
    private $cronjobs;
    private $pluginsService;
    private $translator;

    public function __construct($em, $dispatcher, $scheduler, $systemPreferences, $pluginsService, $translator)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->scheduler = $scheduler;
        $this->systemPreferences = $systemPreferences;
        $reflection = new \ReflectionClass($this);
        $this->classDir = $reflection->getFileName();
        $this->pluginDir = dirname($this->classDir).$this->pluginDir;
        $this->pluginsService = $pluginsService;
        $this->translator = $translator;
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);
        $this->em->getProxyFactory()->generateProxyClasses(
            $this->getClasses(),
            $this->pluginDir.'library/Proxy'
        );

        $adapter = new Gateway();
        $adapter->setName('Offline');
        $adapter->setValue('offline');
        $this->em->persist($adapter);
        $this->em->flush();

        $this->dispatcher->dispatch('newscoop_paywall.adapters.register', new GenericEvent());

        $this->addJobs();
        $this->systemPreferences->PaywallMembershipNotifyEmail = $this->systemPreferences->EmailFromAddress;
        $this->systemPreferences->PaywallMembershipNotifyFromEmail = $this->systemPreferences->EmailFromAddress;
        $this->systemPreferences->PaywallEmailNotifyEnabled = 0;
        $this->setPermissions();
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->dispatcher->dispatch('newscoop_paywall.adapters.register', new GenericEvent());

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses(
            $this->getClasses(),
            $this->pluginDir.'library/Proxy'
        );

        $this->setPermissions();
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
        $this->removeJobs();
        $this->removeSettings();
        $this->removePermissions();
    }

    /**
     * Clean up system preferences.
     */
    private function removeSettings()
    {
        $this->systemPreferences->delete('PaywallMembershipNotifyEmail');
        $this->systemPreferences->delete('PaywallEmailNotifyEnabled');
        $this->systemPreferences->delete('PaywallMembershipNotifyFromEmail');
    }

    /**
     * Add plugin cron jobs.
     */
    private function addJobs()
    {
        $this->setCronJobs();
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->registerJob($jobName, $jobConfig);
        }
    }

    /**
     * Remove plugin cron jobs.
     */
    private function removeJobs()
    {
        $this->setCronJobs();
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->removeJob($jobName, $jobConfig);
        }
    }

    private function setCronJobs()
    {
        $queryBuilder = $this->em->getRepository('Newscoop\Entity\Publication')
            ->createQueryBuilder('p')
            ->select('a.name')
            ->leftJoin('p.defaultAlias', 'a')
            ->setMaxResults(1);

        $alias = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!isset($alias['name'])) {
            throw new \RuntimeException('There is no alias defined! At least one alias needs to be defined.');
        }

        $this->cronjobs = array(
            'Sends email notifications for expiring subscriptions' => array(
                'command' => sprintf(
                    '%s paywall:notifier:expiring %s',
                    realpath($this->pluginDir.'application/console'),
                    $alias['name']
                ),
                'schedule' => '0 2 * * *',
            ),
        );
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_newscoop_paywall_bundle' => array('install', 1),
            'plugin.update.newscoop_newscoop_paywall_bundle' => array('update', 1),
            'plugin.remove.newscoop_newscoop_paywall_bundle' => array('remove', 1),
        );
    }

    /**
     * Remove plugin permissions.
     */
    private function removePermissions()
    {
        $this->pluginsService->removePluginPermissions($this->pluginsService->collectPermissions($this->translator->trans('paywall.title')));
    }
    /**
     * Collect plugin permissions.
     */
    private function setPermissions()
    {
        $this->pluginsService->savePluginPermissions($this->pluginsService->collectPermissions($this->translator->trans('paywall.title')));
    }

    private function getClasses()
    {
        return array(
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Subscription'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\SubscriptionSpecification'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Gateway'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\UserSubscription'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Trial'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Discount'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Duration'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Order'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Modification'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Currency'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\SubscriptionTranslation'),
            $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Payment'),
        );
    }
}
