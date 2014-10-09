<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Events;

use Symfony\Component\EventDispatcher\GenericEvent as SymfonyGenericEvent;

/**
 * Collect external adapters
 */
class AdaptersEvent extends SymfonyGenericEvent
{
    /**
     * Adapters array
     *
     * @var array
     */
    public $adapters = array();

    /**
     * Register new adapter
     *
     * @param string $name
     * @param array  $adapter
     */
    public function registerAdapter($name, array $adapter)
    {
        $this->adapters[$name] = $adapter;
    }

    /**
     * Get all adapters
     *
     * @return array
     */
    public function getAdapters()
    {
        return $this->adapters;
    }
}
