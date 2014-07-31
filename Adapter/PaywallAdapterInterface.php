<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Adapter;

use Newscoop\PaywallBundle\Services\PaywallService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface PaywallAdapterInterface
{   
    /**
     * Apply injected services
     * @param PaywallService; $subscriptionService 
     */
    public function __construct(PaywallService $subscriptionService);

    /**
     * Process callback request
     * @param array $params Parameters
     * @return Response 
     */
    public function proccess($params = array());

    /**
     * Set request to process
     * @param Request $request
     */
    public function setRequest(Request $request);
}