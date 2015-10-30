<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Services\PaywallService;

abstract class AbstractAdapter implements PaywallAdapterInterface
{
    protected $subscriptionService;

    protected $request;

    public function __construct(PaywallService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
