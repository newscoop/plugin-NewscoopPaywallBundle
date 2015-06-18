<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;

class ManageSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/manage", options={"expose"=true})
     * @Template()
     */
    public function manageAction(Request $request)
    {
        $subscription = new Subscriptions();
        $form = $this->createForm('subscriptionconf', $subscription);
        $em = $this->getDoctrine()->getManager();
        $criteria = new SubscriptionCriteria();
        $subscriptions = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->getListByCriteria($criteria, true)
            ->getResult();

        return array(
            'subscriptions' => $subscriptions,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/manage/delete/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array('id' => $id));
            $subscription->setIsActive(false);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }
    }
}
