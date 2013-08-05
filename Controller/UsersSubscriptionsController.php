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
use Newscoop\PaywallBundle\Form\SubscriptionConfType;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Entity\Subscription_specification;
use Doctrine\ORM\Query\Expr\Join;

class UsersSubscriptionsController extends Controller
{
    /**
     * @Route("/admin/paywall_plugin/users-subscriptions")
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
     * @Route("/admin/paywall_plugin/users-subscriptions/remove/{id}")
     */
    public function removeAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $service = $this->get('subscription.service');
            //TODO

            return new Response(json_encode(array('status' => true)));
        }
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/edit/{id}")
     */
    public function editAction(Request $request, $id)
    {
        $service = $this->get('subscription.service');

        //TODO
    }

    /**
     * @Route("/admin/paywall_plugin/users-subscriptions/details/{id}")
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
}