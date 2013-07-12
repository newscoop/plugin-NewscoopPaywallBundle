<?php

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
         $subscription = new Subscriptions();
         $form = $this->createForm('subscriptionconf', $subscription);

         if ($request->isMethod('POST')) {
            $form->bind($request);

            if($form->isValid()) {
                $data = $request->request->get($form->getName());
                $name = $data['name'];
                $type = $data['type'];
                $range = $data['range'];
                $price = $data['price'];
                $currency = $data['currency'];
                $subscription->setName($name);
                $subscription->setType($type);
                $subscription->setRange($range);
                $subscription->setPrice($price);
                $subscription->setCurrency($currency);
                $em = $this->getDoctrine()->getManager();
                $em->persist($subscription);
                $em->flush();
                return $this->redirect($this->generateUrl('newscoop_paywall_admin_admin'));
            }
        }
        return array(
          'form' => $form->createView()
          );
    } 

    /**
     * @Route("/admin/paywall_plugin/test")
     * @Template()
     */
    public function testAction(Request $request)
    {
       $name = strtolower($request->request->get('nameSub'));

       $em = $this->getDoctrine()->getManager();
       $entity = $em->getRepository('NewscoopPaywallBundle:Subscriptions')
       ->findOneBy(array('name' => $name));
       
       if($entity) {
           return new Response(json_encode(array('status' => true)));
       } else {
           return new Response(json_encode(array('status' => false)));
           throw $this->createNotFoundException('Brak takiej subskrypcji');
       }
   }
}