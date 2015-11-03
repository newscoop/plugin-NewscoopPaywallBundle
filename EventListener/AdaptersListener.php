<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\PaywallBundle\Events\AdaptersEvent;
use Symfony\Component\Finder\Finder;
use Newscoop\PaywallBundle\Entity\Settings;

/**
 * Adapters listener.
 */
class AdaptersListener
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Register external adapters.
     *
     * @param AdaptersEvent $event
     */
    public function registerExternalAdapters(AdaptersEvent $event)
    {
        $this->installAdapters($event);
    }

    /**
     * Install external adapters.
     */
    private function installAdapters(AdaptersEvent $event)
    {
        $finder = new Finder();
        $reflection = new \ReflectionClass($this);

        try {
            $pluginsDir = dirname($reflection->getFileName()).'/../../../';

            $iterator = $finder
                ->ignoreUnreadableDirs()
                ->files()
                ->name('*Adapter.php')
                ->in($pluginsDir.'*/*/Adapter/PaywallAdapters/')
                ->in(dirname($reflection->getFileName()).'/../Adapter/');

            foreach ($iterator as $file) {
                $classNamespace = str_replace(realpath($pluginsDir), '', substr($file->getRealPath(), 0, -4));
                $namespace = str_replace('/', '\\', $classNamespace);
                $adapterName = substr($file->getFilename(), 0, -4);

                $oneAdapter = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
                    ->findOneBy(array('value' => $adapterName));

                $event->registerAdapter($adapterName, array(
                    'class' => $namespace,
                ));

                if (!$oneAdapter) {
                    $adapter = new Settings();
                    $adapter->setName(str_replace('Adapter.php', '', $file->getFilename()));
                    $adapter->setValue($adapterName);
                    if ($adapterName !== 'PaypalAdapter') {
                        $adapter->setIsActive(false);
                    }

                    $this->em->persist($adapter);
                }
            }

            $this->em->flush();
        } catch (\Exception $e) {
        }
    }
}
