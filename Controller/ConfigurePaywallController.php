<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Newscoop\PaywallBundle\Form\Type\SettingsFormType;
use Newscoop\PaywallBundle\Permissions;

class ConfigurePaywallController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/configure-paywall", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $this->hasPermission(Permissions::CONFIGURE);
        $em = $this->getDoctrine()->getManager();
        $preferencesService = $this->container->get('system_preferences_service');
        $translator = $this->container->get('translator');
        $active = $em->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
            ->findOneBy(array(
                'isActive' => true,
            ));

        $form = $this->container->get('form.factory')->create(new SettingsFormType(), array(
            'notificationEmail' => $preferencesService->PaywallMembershipNotifyEmail,
            'enableNotify' => $preferencesService->PaywallEmailNotifyEnabled == '1' ? true : false,
            'notificationFromEmail' => $preferencesService->PaywallMembershipNotifyFromEmail,
            'currency' => $preferencesService->PaywallDefaultCurrency,
        ));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $preferencesService->set('PaywallMembershipNotifyEmail', $data['notificationEmail']);
                $preferencesService->set('PaywallEmailNotifyEnabled', $data['enableNotify']);
                $preferencesService->set('PaywallMembershipNotifyFromEmail', $data['notificationFromEmail']);
                $preferencesService->set('PaywallDefaultCurrency', $data['currency']);

                if (is_numeric($data['adapter'])) {
                    $settings = $em->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
                        ->findOneBy(array(
                            'id' => $data['adapter'],
                        ));

                    $all = $em->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
                        ->findAll();

                    foreach ($all as $value) {
                        $value->setActive(false);
                    }

                    $settings->setActive(true);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', $translator->trans('paywall.success.settingssaved'));

                return $this->redirect($this->generateUrl('newscoop_paywall_configurepaywall_index'));
            } else {
                $this->get('session')->getFlashBag()->add('error', $translator->trans('paywall.error.settingserror'));

                return $this->redirect($this->generateUrl('newscoop_paywall_configurepaywall_index'));
            }
        }

        if ($request->isXmlHttpRequest()) {
            $inactive = $em->getRepository('Newscoop\PaywallBundle\Entity\Gateway')
                ->findBy(array(
                    'isActive' => false,
                ));

            $adapters = array();
            foreach ($inactive as $value) {
                $adapters[] = array(
                    'id' => $value->getId(),
                    'text' => $value->getName(),
                );
            }

            return new Response(json_encode($adapters));
        }

        return array(
            'form' => $form->createView(),
            'current' => $active->getName(),
        );
    }
}
