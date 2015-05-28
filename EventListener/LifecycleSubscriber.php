<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\PaywallBundle\Entity\Settings;
use Newscoop\PaywallBundle\Events\AdaptersEvent;

/**
 * Event lifecycle management.
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;
    private $dispatcher;
    private $scheduler;
    private $systemPreferences;

    public function __construct($em, $dispatcher, $scheduler, $systemPreferences)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->scheduler = $scheduler;
        $this->systemPreferences = $systemPreferences;

        $appDirectory = realpath(__DIR__.'/../../../../application/console');
        $this->cronjobs = array(
            'Sends email notifications for expiring subscriptions' => array(
                'command' => $appDirectory.' paywall:notifier:expiring',
                'schedule' => '0 2 * * *',
            ),
        );
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__.'/../../../../library/Proxy');
        /*$adapter = new Settings();
        $adapter->setName('Paypal');
        $adapter->setValue('PaypalAdapter');
        $this->em->persist($adapter);
        $this->em->flush();

        $this->dispatcher->dispatch('newscoop_paywall.adapters.register', new AdaptersEvent($this, array()));

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__.'/../../../../library/Proxy');
        $this->addJobs();
        $this->systemPreferences->PaywallMembershipNotifyEmail = $this->systemPreferences->EmailFromAddress;
        $this->systemPreferences->PaywallMembershipNotifyFromEmail = $this->systemPreferences->EmailFromAddress;
        $this->systemPreferences->PaywallEmailNotifyEnabled = 0;*/
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->dispatcher->dispatch('newscoop_paywall.adapters.register', new AdaptersEvent($this, array()));

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__.'/../../../../library/Proxy');
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
        $this->removeJobs();
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
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->registerJob($jobName, $jobConfig);
        }
    }

    /**
     * Remove plugin cron jobs.
     */
    private function removeJobs()
    {
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->removeJob($jobName, $jobConfig);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_newscoop_paywall_bundle' => array('install', 1),
            'plugin.update.newscoop_newscoop_paywall_bundle' => array('update', 1),
            'plugin.remove.newscoop_newscoop_paywall_bundle' => array('remove', 1),
        );
    }

    private function getClasses()
    {
        return array(
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Subscriptions'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\SubscriptionSpecification'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Settings'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\UserSubscription'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Trial'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Discount'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Duration'),
          $this->em->getClassMetadata('Newscoop\PaywallBundle\Entity\Action'),
        );
    }
}
