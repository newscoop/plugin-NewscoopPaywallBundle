<?php

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Entity\Subscriptions;

class ManageSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/manage")
     * @Template()
     */
    public function manageAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $subscriptions = $em->getRepository('NewscoopPaywallBundle:Subscriptions')
            ->findAll();

        return $this->render(
            'NewscoopPaywallBundle:ManageSubscriptions:manage.html.twig', array(
                'subscriptions' => $subscriptions,
                ));
    }
}