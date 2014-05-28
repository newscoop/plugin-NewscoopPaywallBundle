<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Newscoop\PaywallBundle\Entity\Settings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        //install custom adapters located in other plugins (dir: Adapter/PaywallAdapters)
        $this->installCustomAdapters();
        $this->installAdapters();

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->installCustomAdapters();
        $this->installAdapters();

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
    }

    /**
     * Install (copy) custom adapters
     * @return void
     * @throws Exception
     */
    private function installCustomAdapters()
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $pluginsDir = __DIR__ . '/../../../../';

        $iterator = $finder
            ->ignoreUnreadableDirs()
            ->files()
            ->name('*Adapter.zip')
            ->in($pluginsDir . '*/*/Adapter/PaywallAdapters/');

        foreach ($iterator as $file) {
            $zip = new \ZipArchive();
            try {
                if ($zip->open($file->getRealpath()) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $zip->extractTo(__DIR__ . '/../Adapter/', array($zip->getNameIndex($i)));
                    }

                    $zip->close();
                }
            } catch (\Exception $e) {
                throw new \Exception('Error extracting zip with adapter!');
            }
        }
    }

    /**
     * Install all adapters
     * @return void
     * @throws Exception
     */
    private function installAdapters()
    {
        $fs = new Filesystem();
        $finder = new Finder();

        try {
            $iterator = $finder
                ->files()
                ->name('*Adapter.php')
                ->in(__DIR__  . '/../Adapter/');

            foreach ($iterator as $file) {
                $oneAdapter = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
                    ->findOneBy(array('value' => substr($file->getFilename(), 0, -4)));

                if (!$oneAdapter) {
                    $adapter = new Settings();
                    $adapter->setName(str_replace("Adapter.php", "", $file->getFilename()));
                    $adapter->setValue(substr($file->getFilename(), 0, -4));
                    if (substr($file->getFilename(), 0, -4) !== 'PaypalAdapter') {
                        $adapter->setIsActive(false);
                    }

                    $this->em->persist($adapter);
                }
            }

            $this->em->flush();
        } catch (\Exception $e) {
            throw new \Exception('Error installing adapters!');
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_newscoop_paywall_bundle' => array('install', 1),
            'plugin.update.newscoop_newscoop_paywall_bundle' => array('update', 1),
            'plugin.remove.newscoop_ dnewscoop_paywall_bundle' => array('remove', 1),
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
        );
    }
}
