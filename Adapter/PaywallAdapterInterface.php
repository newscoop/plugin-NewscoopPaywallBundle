<?php

/**
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Adapter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface PaywallAdapterInterface
{
    /**
     * Process callback request.
     *
     * @param array $params Parameters
     *
     * @return Response
     */
    public function process($params = array());
}
