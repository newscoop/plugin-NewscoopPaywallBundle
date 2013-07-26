<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Entity\Subscriptions;

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
        $subscriptions = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findBy(array('is_active' => true));

        return array(
            'subscriptions' => $subscriptions,
            'form' => $form->createView()
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

    /**
     * @Route("/admin/paywall_plugin/manage/edit")
     * @Method("PATCH")
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                       ->findOneBy(array('id' => $request->get('row_id')));
        $form = $this->createForm('subscriptionconf', $subscription);
        if ($request->isMethod('PATCH')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();

                if ($request->isXmlHttpRequest()) {
                    return array('status' => true);
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    return array(
                        'status' => false,
                        'errors' => json_encode($this->getErrorMessages($form))
                    );
                }
            }
        }
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {      
        $errors = array();
        if (count($form) > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();   
        }

        return $errors;
    }
}