<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Events\AdaptersEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * PaywallManager class manages paywall adapters.
 */
class PaywallManager
{
    /** @var EntityManager */
    private $em;

    /** @var PaywallService */
    private $subscriptionService;

    /** @var EventDispatcher */
    private $dispatcher;

    /**
     * Apply entity manager and injected services.
     *
     * @param EntityManager  $em
     * @param PaywallService $subscriptionService
     */
    public function __construct(EntityManager $em, PaywallService $subscriptionService, EventDispatcher $dispatcher)
    {
        $this->em = $em;
        $this->subscriptionService = $subscriptionService;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get adapters, if adapter doesn't exist, use default one.
     *
     * @return PaypalAdapter|object
     */
    public function getAdapter()
    {
        $adaptersEvent = $this->dispatcher->dispatch('newscoop_paywall.adapters.register', new AdaptersEvent($this, array()));
        $settings = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Settings')->findOneBy(array(
            'is_active' => true,
        ));

        $adapter = null;
        if (array_key_exists($settings->getValue(), $adaptersEvent->adapters)) {
            $adapter = $adaptersEvent->adapters[$settings->getValue()]['class'];
        }

        if (!class_exists($adapter)) {
            return new \Newscoop\PaywallBundle\Adapter\PaypalAdapter($this->subscriptionService);
        }

        return new $adapter($this->subscriptionService);
    }
}
