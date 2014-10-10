<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\PaywallBundle\Events\AdaptersEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Newscoop\PaywallBundle\Entity\Settings;

/**
 * Adapters listener
 */
class AdaptersListener
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Register external adapters
     *
     * @param AdaptersEvent $event
     */
    public function registerExternalAdapters(AdaptersEvent $event)
    {
        $this->installAdapters($event);
    }

    /**
     * Install external adapters
     * @return void
     * @throws Exception
     */
    private function installAdapters(AdaptersEvent $event)
    {
        $fs = new Filesystem();
        $finder = new Finder();

        try {
            $pluginsDir = __DIR__ . '/../../../';

            $iterator = $finder
                ->ignoreUnreadableDirs()
                ->files()
                ->name('*Adapter.php')
                ->in($pluginsDir . '*/*/Adapter/PaywallAdapters/')
                ->in(__DIR__  . '/../Adapter/');

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
                    $adapter->setName(str_replace("Adapter.php", "", $file->getFilename()));
                    $adapter->setValue($adapterName);
                    if ($adapterName !== 'PaypalAdapter') {
                        $adapter->setIsActive(false);
                    }

                    $this->em->persist($adapter);
                }
            }

            $this->em->flush();
        } catch (\Exception $e) {}
    }
}
