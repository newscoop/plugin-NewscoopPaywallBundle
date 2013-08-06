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

class UsersSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/users-subscriptions", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $service = $this->get('subscription.service');

        return array(
            'subscriptions' => $service->getByAll(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/delete/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $service = $this->get('subscription.service');
            $service->removeById($id);

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/remove/{type}/{id}")
     */
    public function removeAction(Request $request, $type, $id)
    {
        if ($request->isMethod('POST')) {
            $service = $this->get('subscription.service');
            $em = $this->getDoctrine()->getManager();
            $subscription = $this->findByType($em, $type, $id);
            $em->remove($subscription);
            $em->flush();

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{type}/{id}")
     * @Template()
     */
    public function editAction(Request $request, $type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $subscription = $this->findByType($em, $type, $id);

        $form = $this->createForm('detailsForm', $subscription);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->flush();
            }
        }

        return array(
            'form' => $form->createView(),
            'id' => $subscription->getId(),
            'type' => $type,
            'subscription' => $subscription->getSubscription()->getId(),
            'name' => $subscription->getName(),
            'language' => $subscription->getLanguage()->getName(),
        );
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/details/{id}", options={"expose"=true})
     * @Template()
     */
    public function detailsAction(Request $request, $id)
    {
        $service = $this->get('subscription.service');

        return array(
            'issues' => $service->getIssues($id),
            'sections' => $service->getSections($id),
            'articles' => $service->getArticles($id),
        );
    }

    /**
     * Finds proper Entity object by given Type
     *
     * @param Doctrine\ORM\EntityManager $em
     * @param string $type                   Subscription type
     * @param string $id                     Subscription id
     *
     * @return Entity object
     */
    private function findByType($em, $type, $id) {

        if ($type === 'section') {
            $subscription = $em->getRepository('Newscoop\Subscription\Section')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        if ($type === 'issue') {
            $subscription = $em->getRepository('Newscoop\Subscription\Issue')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        if ($type === 'article') {
            $subscription = $em->getRepository('Newscoop\Subscription\Article')
                ->findOneBy(array(
                    'id' => $id,
                ));
        }

        return $subscription;
    }
}