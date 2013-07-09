<?php

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin")
     * @Template()
     */
    public function adminAction(Request $request)
    {
    	$this->container->get('dispatcher')->dispatch('plugin.install', new \Newscoop\EventDispatcher\Events\GenericEvent($this, array(
                'Paywall Plugin' => ''
            )));
    	return array();
    }
}
