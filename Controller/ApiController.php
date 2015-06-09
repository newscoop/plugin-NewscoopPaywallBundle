<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;

/**
 * API controller.
 */
class ApiController extends FOSRestController
{
    /**
     * @Route("/api/paywall/pricelist/{currency}.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_pricelist")
     *
     * @Method("GET")
     * @View()
     */
    public function listAction(Request $request, $currency)
    {
        $em = $this->get('em');
        $criteria = new SubscriptionCriteria();
        $criteria->currency = $currency;
        $paywallService = $this->get('paywall.subscription.service');
        $list = $paywallService->getSubscriptionsByCriteria($criteria);
        $paginator = $this->get('newscoop.paginator.paginator_service');
        $paginator->setUsedRouteParams(array('currency' => $currency));
        $priceList = $paginator->paginate(
            $list->items,
            array(
                'distinct' => false,
            )
        );

        return $priceList;
    }

    /**
     * @Route("/api/paywall/my-subscriptions.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_my")
     *
     * @Method("GET")
     * @View()
     */
    public function myAction(Request $request)
    {
        $em = $this->get('em');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        $criteria = new SubscriptionCriteria();
        $criteria->user = $user;
        $paywallService = $this->get('paywall.subscription.service');
        $query = $paywallService->getUserSubscriptionsByCriteria($criteria, true);

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $list = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        return $list;
    }

    /**
     * @Route("/api/paywall/discounts.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_discount")
     *
     * @Method("GET")
     * @View()
     */
    public function discountAction(Request $request)
    {
        $em = $this->get('em');
        $query = $em->getRepository('Newscoop\PaywallBundle\Entity\Discount')
            ->findActive();

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $discounts = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        return $discounts;
    }

    /**
     * @Route("/api/paywall/currencies.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_currency")
     *
     * @Method("GET")
     * @View()
     */
    public function currencyAction(Request $request)
    {
        $currencyRepository = $this->get('newscoop_paywall.currency.repository');
        $query = $currencyRepository->findActive();

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $currencies = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        return $currencies;
    }
}
