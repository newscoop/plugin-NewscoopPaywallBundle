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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Entity\Settings;

class ConfigurePaywallController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/configure-paywall", options={"expose"=true})
     * @Route("/admin/paywall_plugin/configure-paywall/{id}", name="newscoop_paywall_getconfiguration", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $inactive = $em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
            ->findBy(array(
                'is_active' => false
            ));

        $active = $em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
            ->findOneBy(array(
                'is_active' => true
            ));

        if ($id) {
            $settings = $em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
                ->findOneBy(array(
                    'id' => $id,
                ));
            $all = $em->getRepository('Newscoop\PaywallBundle\Entity\Settings')
                ->findAll();

            foreach ($all as $value) {
                $value->setIsActive(false);
            }

            $settings->setIsActive(true);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }

        $adapters = array();
        foreach ($inactive as $value) {
            $adapters[] = array(
                'id' => $value->getId(),
                'text' => $value->getName()
            );
        }

        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode($adapters));
        }

        return array(
            'current' => $active->getName()
        );
    }
}
