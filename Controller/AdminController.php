<?php

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Form\SubscriptionConfType;
use Newscoop\PaywallBundle\Entity\Subscriptions;

class AdminController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin")
     * @Template()
     */
    public function adminAction(Request $request)
    {
    	/*$this->container->get('dispatcher')->dispatch('plugin.install', new \Newscoop\EventDispatcher\Events\GenericEvent($this, array(
                'Paywall Plugin' => ''
            )));*/
        //TODO
        /*zrób też ajaxa który sprawdzi czy nazwa do subskrypcji nie jest zajęta
         i jak jest ok to niech podłetla ją na zialono (tekst w inpucie ma być zielony) jak nie jest ok to ma byc na czerwono i alert js'owy o ttym że jest zła
        */
        $em = $this->getDoctrine()->getManager();
        $subscription = new Subscriptions();
        $form = $this->createForm('subscriptionconf', $subscription);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if($form->isValid()) {
                return new RedirectResponse($this->generateUrl('newscoop_paywall_admin_admin'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }     
}