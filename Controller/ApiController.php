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
     * @Route("/api/paywall/pricelist/{currency}/{locale}.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_pricelist")
     *
     * @Method("GET")
     * @View()
     */
    public function listAction(Request $request, $currency, $locale = null)
    {
        $criteria = new SubscriptionCriteria();
        $criteria->locale = $locale;
        $paywallService = $this->get('paywall.subscription.service');
        $currencyContext = $this->get('newscoop_paywall.currency_context');
        $paginator = $this->get('newscoop.paginator.paginator_service');
        $criteria->type = $request->query->get('type');
        $list = $paywallService->getSubscriptionsByCriteria($criteria);
        $paginator->setUsedRouteParams(array('currency' => $currency));
        $currencyContext->setCurrency($currency);

        $priceList = $paginator->paginate(
            $list->items,
            array(
                'distinct' => false,
            )
        );

        return $priceList;
    }

    /**
     * @Route("/api/paywall/my-subscriptions/{locale}.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_my")
     *
     * @Method("GET")
     * @View()
     */
    public function myAction(Request $request, $locale = null)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        $criteria = new SubscriptionCriteria();
        $criteria->user = $user;
        $criteria->locale = $locale;
        $paywallService = $this->get('paywall.subscription.service');
        $query = $paywallService->getMySubscriptionsByCriteria($criteria, true);
        $paginator = $this->get('newscoop.paginator.paginator_service');
        $list = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        $list['items'] = $paywallService->filterMySubscriptions($list['items']);

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
        $query = $currencyRepository->findAllAvailable();

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $currencies = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        return $currencies;
    }

    /**
     * @Route("/api/paywall/gateways.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_paywall_gateways")
     *
     * @Method("GET")
     * @View()
     */
    public function gatewaysAction(Request $request)
    {
        $provider = $this->get('newscoop_paywall.method_provider');
        $query = $provider->getEnabledMethods();
        $paginator = $this->get('newscoop.paginator.paginator_service');
        $gateways = $paginator->paginate(
            $query,
            array(
                'distinct' => false,
            )
        );

        return $gateways;
    }
}
