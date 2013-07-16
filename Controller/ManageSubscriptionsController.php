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

        $em = $this->getDoctrine()->getManager();
        $subscriptions = $em->getRepository('NewscoopPaywallBundle:Subscriptions')
            ->findBy(array('is_active' => true));

        return array('subscriptions' => $subscriptions);
    }

    /**
     * @Route("/admin/paywall_plugin/manage/delete/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NewscoopPaywallBundle:Subscriptions')
                ->findOneBy(array('id' => $id));
            $entity->setIsActive(false);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/manage/edit")
     */
    public function editAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $id = $this->get('request')->request->get('row_id');
            $value = $this->get('request')->request->get('value');
            $column = $this->get('request')->request->get('column');
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NewscoopPaywallBundle:Subscriptions')
                   ->findOneBy(array('id' => $id));
            switch($column){
                case "1":
                    $entity->setName($value);
                    break;
                case "3":
                    $entity->setRange($value);
                    break;
                case "4":
                    $entity->setPrice($value);
                    break;
                case "5":
                    $entity->setCurrency($value);
                    break;
            }
            $em->flush();
            
            return new Response(json_encode(array('data' => $value)));
        }
    }
}