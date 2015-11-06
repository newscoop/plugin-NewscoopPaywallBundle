<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\PaywallBundle\Entity\Gateway;
use Doctrine\ORM\EntityManager;

/**
 * Adapters listener.
 */
class AdaptersListener
{
    /**
     * Entity Manager.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Paywall gateways.
     *
     * @var array
     */
    private $gateways;

    /**
     * Construct.
     *
     * @param EntityManager $entityManager
     * @param array         $gateways
     */
    public function __construct(EntityManager $entityManager, array $gateways)
    {
        $this->entityManager = $entityManager;
        $this->gateways = $gateways;
    }

    /**
     * Register external adapters.
     */
    public function registerExternalAdapters()
    {
        $this->installAdapters();
    }

    /**
     * Install external adapters.
     */
    private function installAdapters()
    {
        $activeAdapter = $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
                ->findOneByisActive(true);

        foreach ((array) $this->gateways as $name => $gateway) {
            $adapter = $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
                ->findOneByValue($name);

            if (!$adapter) {
                $adapter = new Gateway();
                $adapter->setName($name);
                $adapter->setValue($name);

                if ($activeAdapter && $name !== $activeAdapter->getValue()) {
                    $adapter->setActive(false);
                }

                $this->entityManager->persist($adapter);
            }
        }

        $this->entityManager->flush();
    }
}
