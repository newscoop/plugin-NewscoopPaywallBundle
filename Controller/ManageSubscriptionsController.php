<?php
/**
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @package Newscoop\PaywallBundle
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

class ManageSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/manage")
     * @Template()
     */
    public function manageAction(Request $request)
    {
        $form = $this->createForm('subscriptionEdit');
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
     */
    public function editAction(Request $request)
    {
        $form = $this->createForm('subscriptionEdit');
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if($form->isValid()) {
                $data = $request->request->get($form->getName());
                $em = $this->getDoctrine()->getManager();
                $column = $request->get('column');
                $value = $request->get('value');

                $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                       ->findOneBy(array('id' => $request->get('row_id')));

                //TODO: We need validation here.
                //steps: 
                //* we need form
                //* we need request with ajax with PATH method - then symfony will validate only existing properties - more here: https://github.com/symfony/symfony/pull/7849/files
                //* here is how you can make PATH method with ajax and symfony: http://symfony.com/doc/current/cookbook/routing/method_parameters.html
                //* just add _method=PATH to your request params.
                //fyi: You can define in form what method is alowed: http://symfony.com/doc/current/book/forms.html#book-forms-changing-action-and-method
                //
                // remove that comment after implementation
                switch($column){
                    case "1":
                        $subscription->setName($value);
                        break;
                    case "3":
                        $subscription->setRange($value);
                        break;
                    case "4":
                        $subscription->setPrice($value);
                        break;
                    case "5":
                        $subscription->setCurrency($value);
                        break;
                }
            }

            $em->flush();
                
            return new Response(json_encode(array('data' => $value)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/manage/update/{id}")
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneBy(array(
                'id' => $id
            ));
        $form = $this->createForm('subscriptionconf', $subscription);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if($form->isValid()) {
                $data = $request->request->get($form->getName());
                $subscription->setName($data['name']);
                $subscription->setType($data['type']);
                $subscription->setRange($data['range']);
                $subscription->setPrice($data['price']);
                $subscription->setCurrency($data['currency']);
                $em->persist($subscription);
                $em->flush();
                
                return $this->redirect($this->generateUrl('newscoop_paywall_managesubscriptions_manage'));
            }
        }

        return $this->render('NewscoopPaywallBundle:ManageSubscriptions:update.html.twig',
            array(
                'form' => $form->createView(),
                'subscription_id' => $id
            ));
    }
}