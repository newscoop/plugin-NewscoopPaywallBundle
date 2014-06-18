<?php

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Form\Type\MembershipFormType;
use Newscoop\PaywallBundle\Form\Type\MembershipDataFormType;
use Symfony\Component\Form\Form;
use Newscoop\PaywallBundle\Entity\Trial;
use Newscoop\PaywallBundle\Subscription\SubscriptionData;

class MembershipController extends Controller
{
    /**
     * Get callback response from paywall/payment provider and proccess it.
     *
     * @Route("/paywall/membership/get")
     */
    public function getSubscriptionsAction(Request $request)
    {
        $userService = $this->container->get('user');
        $translator = $this->container->get('translator');
        $em = $this->container->get('em');
        $user = $userService->getCurrentUser();
        $subscriptionService = $this->container->get('subscription.service');
        $membershipService = $this->container->get('newscoop_paywall.membership');
        $errors = array();
        $messages = array();
        $upgrade = false;
        $buyOnly = false;
        $selected = null;
        $userSubscription = $subscriptionService->getOneByUser($user);
        $defaultSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
        ->findOneBy(array(
            'is_default' => true
        ));

        $subs = array();
        foreach ($subscriptionService->getSubscriptionsConfig() as $key => $value) {
            $subs[$value->getId()] = $value->getName();
        }

        $form = $this->container->get('form.factory')->create(new MembershipFormType(), array(
            'customer_id' => $user->getAttribute('customer_id') ?: null,
            'membershipType' => !$userSubscription ? null : $userSubscription->getSubscription()->getId()
        ), array('subs' => $subs));

        $adapter = $this->get('newscoop.paywall.adapter');
        $adapter->setRequest($request);
        $adapterResult = $adapter->proccess();

        $buyOnly = $subscriptionService->isValidTrial($user);
        if ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user) && !$adapterResult['status']) {
                //trzeba okreslic defaultowa subscrybcje do jakiej bedzie downgrade jesli nie ma triala
                // (dodac tylko UI do tego, reszta jest)
                //

            $publication = 0;
            foreach ($defaultSubscription->getSpecification() as $key => $value) {
               $publication = $value->getPublication();
            }

            $subscriptionData = new SubscriptionData(array(
                'userId' => $user,
                'subscriptionId' => $defaultSubscription,
                'publicationId' => $publication,
                'toPay' => $defaultSubscription->getPrice(),
                'days' => $defaultSubscription->getRange(),
                'currency' => $defaultSubscription->getCurrency(),
                'type' => 'T', // set trial period by default for all users
                        //if user paid fetch xml and check if its valid, if it is, change status to P
                'active' => false
                ), $userSubscription);
            $subscriptionService->update($userSubscription, $subscriptionData);
            $em->flush();

            $buyOnly = true;
            $errors[] = $translator->trans('paywall.msg.membershipexpired');
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $subscriptionSpec = $subscriptionService->getOneSubscriptionSpecification($data['membershipType']);
                    //actual subscription user is subscribed for
                $isSubscribed = false;
                $userSubscriptionSubscribed = $subscriptionService->getOneByUserAndSubscription($user, $subscriptionSpec->getSubscription());

                if ($userSubscriptionSubscribed) {
                    $isSubscribed = true;
                }

                    //if user has subscription, check which and upgrade/downgrade, leave the same trial time
                if ($userSubscription && !$isSubscribed) {
                    if (!$subscriptionService->userHadTrial($user)) {
                        $subscriptionData = new SubscriptionData(array(
                            'userId' => $user,
                            'subscriptionId' => $subscriptionSpec->getSubscription(),
                            'publicationId' => $subscriptionSpec->getPublication(),
                            'toPay' => $subscriptionSpec->getSubscription()->getPrice(),
                            'days' => $subscriptionSpec->getSubscription()->getRange(),
                            'currency' => $subscriptionSpec->getSubscription()->getCurrency(),
                            'type' => 'T', // set trial period by default for all users
                            'active' => true
                        ), $userSubscription);
                        $subscriptionService->update($userSubscription, $subscriptionData);
                        $em->flush();

                        $messages[] = $translator->trans('paywall.msg.successchange');

                                //adding trial info to all memberships higher than basic (default one)
                        if ($userSubscription->getSubscription() != $defaultSubscription) {
                                $trial = new Trial();
                                $trial->setUser($user);
                                $trial->setHadTrial(true);
                                $datetime = new \DateTime();
                                $datetime->add(new \DateInterval('P'.$subscriptionSpec->getSubscription()->getRange().'D'));
                                $trial->setFinishTrial($datetime);
                                $em->persist($trial);
                                $em->flush();

                                $userSubscription->setTrial($trial);
                                $em->flush();

                                $messages[] = $translator->trans('paywall.success.addedtrial', array('%trial%' => $subscriptionSpec->getSubscription()->getRange()));

                                $buyOnly = true;
                        }
                    } elseif (($userSubscription->getSubscription() == $defaultSubscription) && $subscriptionService->isTrialActive($user)) {
                        $subscriptionData = new SubscriptionData(array(
                            'userId' => $user,
                            'subscriptionId' => $subscriptionSpec->getSubscription(),
                            'publicationId' => $subscriptionSpec->getPublication(),
                            'toPay' => $subscriptionSpec->getSubscription()->getPrice(),
                            'days' => $subscriptionSpec->getSubscription()->getRange(),
                            'currency' => $subscriptionSpec->getSubscription()->getCurrency(),
                            'type' => 'T', // set trial period by default for all users
                            'active' => true
                        ), $userSubscription);
                        $subscriptionService->update($userSubscription, $subscriptionData);
                        $em->flush();

                        $messages[] = $translator->trans('paywall.msg.successchange');
                    } else {
                        if (!$adapterResult['status']) {
                            $errors[] = $translator->trans('paywall.msg.hadtrial');
                        }

                        if (!$adapterResult['validCode']) {
                            $errors[] = $translator->trans('paywall.msg.providevalid');
                        }
                    }

                    if ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user) && $adapterResult['validCode']) {
                        // if upgrade/downgrade
                        $upgrade = true;
                        $selected = $data['membershipType'];
                        $messages[] = $translator->trans('paywall.msg.surechange');
                    }
                }

                if ($isSubscribed && $adapterResult['validCode'] || ($isSubscribed && !$adapterResult['status'])) {
                    if (array_key_exists('thanks', $adapterResult)) {
                        if ($adapterResult['thanks']) {
                            $messages[] = $translator->trans('paywall.msg.numberaccepted');
                            $buyOnly = false;
                        } else {
                            $errors[] = $translator->trans('paywall.error.alreadymember');
                        }
                    } else {
                        $errors[] = $translator->trans('paywall.msg.alreadyorwrong');
                    }
                }

                if ($isSubscribed && $adapterResult['status'] && !$adapterResult['validCode'] ||
                    !$isSubscribed && $adapterResult['status'] && !$adapterResult['validCode']) {
                    $errors[] = $translator->trans('paywall.msg.invalidcustomerid');
                }

            } else {
                $errors[] = $translator->trans('paywall.msg.buyone');
                $buyOnly = true;
            }
        }

        if (!$membershipService->isUserAddressFilledIn($user)) {
            return $this->setDataTemplateVariables($user);
        }

        return $this->setTemplateVariables($user, $errors, $messages, $selected, $upgrade, $buyOnly, $adapterResult['validCode']);
    }

    /**
     * Submit user details
     *
     * @Route("/paywall/membership/details/submit")
     */
    public function submitDataAction(Request $request)
    {
        $userService = $this->container->get('user');
        $user = $userService->getCurrentUser();
        $em = $this->container->get('em');
        $translator = $this->container->get('translator');
        $form = $this->container->get('form.factory')->create(new MembershipDataFormType(), array(), array(
            'translator' => $translator
        ));

        $errors = array();
        $messages = array();
        $dataTpl = array();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $user->setFirstName($data['name']);
                $user->setLastName($data['surname']);
                $user->setPostal($data['postal']);
                $user->setCity($data['city']);
                $user->setStreet($data['street']);
                $user->setState($data['state']);
                $em->flush();

                if (array_key_exists('fancybox', $data)) {
                    if ($data['fancybox'] != null) {
                        $messages[] = $translator->trans('paywall.msg.datasavedfancy');

                        return $this->setDataTemplateVariables($user, $errors, $messages, $dataTpl, "_views/dashboard_membership_fancybox.tpl");
                    }
                }

                return $this->redirect($this->generateUrl('newscoop_paywall_membership_getsubscriptions'));
            } else {
                $data = $form->getData();
                $dataTpl = array(
                    'messages' => array(),
                    'firstName' => $data['name'] ?: $user->getFirstName(),
                    'lastName' => $data['surname'],
                    'street' => $data['street'],
                    'postal' => $data['postal'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'formPath' => $this->generateUrl('newscoop_paywall_membership_submitdata'),
                );

                $errors[] = $this->getErrorMessages($form);
                $dataTpl['errors'] = $errors;

                if (array_key_exists('fancybox', $data)) {
                    if ($data['fancybox'] != null) {
                        return $this->setDataTemplateVariables($user, $errors, $messages, $dataTpl, "_views/dashboard_membership_fancybox.tpl");
                    }
                }
            }
        }

        return $this->setDataTemplateVariables($user, $errors, $messages, $dataTpl);
    }

    /**
     * Get personal details template
     *
     * @Route("/paywall/membership/details/get")
     */
    public function getPersonalDetailsAction()
    {
        $userService = $this->container->get('user');
        $user = $userService->getCurrentUser();

        return $this->setDataTemplateVariables($user, array(), array(), array(), "_views/dashboard_membership_fancybox.tpl");
    }

    /**
     * Set templates variables
     *
     * @param Newscoop\Entity\User $user
     * @param array                $errors       Errors
     * @param array                $messages     Success messages
     * @param boolean|null         $selected     Selected membership
     * @param boolean              $upgrade      If upgrade/downgrade is
     * @param boolean              $buyOnly      Buy only option
     * @param boolean              $validCode    Valid code
     *
     * @return Response
     */
    private function setTemplateVariables($user, $errors = array(), $messages = array(), $selected = null, $upgrade = false, $buyOnly = false, $validCode = false)
    {
        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $subscriptionService = $this->container->get('subscription.service');
        $userSubscription = $subscriptionService->getOneByUser($user);
        $toActivate = $subscriptionService->getSubscriptionToActivate($user, $userSubscription);

        if (!$selected) {
            $selected = !$userSubscription ? null : $userSubscription->getSubscription()->getId();
        }

        $smarty = $templatesService->getSmarty();
        $smarty->assign('errors', $errors);
        $smarty->assign('messages', $messages);
        $smarty->assign('code', $user->getAttribute('customer_id') ?: null);
        $smarty->assign('validCode', $validCode);
        $smarty->assign('active', $selected);
        $smarty->assign('subscriptions', $subscriptionService->getSubscriptionsConfig());
        $smarty->assign('upgrade', $upgrade);
        $smarty->assign('buyOnly', $buyOnly);
        $smarty->assign('membershipExpireDate', $userSubscription && $validCode ? $userSubscription->getExpireAt()->format('d-m-Y') : null);
        $smarty->assign('toActivate', $toActivate ? $toActivate->getSubscription()->getId() : null);
        $smarty->assign('isActiveTrial', $subscriptionService->isTrialActive($user));
        $smarty->assign('trialFinishDate', $subscriptionService->isTrialActive($user) ? $userSubscription->getTrial()->getFinishTrial()->format('d-m-Y') : null);
        $smarty->assign('hadTrial', ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user)));
        $response->setContent($templatesService->fetchTemplate("_views/dashboard_membership.tpl"));
        $response->headers->set('Content-Type', 'text/html');
        $response->setCharset('utf-8');

        return $response;
    }

    /**
     * Set user data template vars
     *
     * @param Newscoop\Entity\User $user         User
     * @param array                $errors       Errors
     * @param array                $messages     Success messages
     * @param array                $data         Template vars
     * @param string|null          $templatePath Template path
     *
     * @return Response
     */
    private function setDataTemplateVariables($user, $errors = array(), $messages = array(), $data = array(), $templatePath = null)
    {
        $templatesService = $this->get('newscoop.templates.service');

        $dataNotSubmitted = array(
            'errors' => $errors,
            'messages' => $messages,
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'street' => $user->getStreet(),
            'postal' => $user->getPostal(),
            'city' => $user->getCity(),
            'state' => $user->getState(),
            'formPath' => $this->generateUrl('newscoop_paywall_membership_submitdata'),
        );

        $response = new Response();
        $response->setContent($templatesService->fetchTemplate($templatePath ?: "_views/dashboard_membership_data.tpl", empty($data) ? $dataNotSubmitted : $data));

        $response->headers->set('Content-Type', 'text/html');
        $response->setCharset('utf-8');

        return $response;
    }

    /**
     * Gets form errors
     *
     * @param Form $form
     *
     * @return array
     */
    private function getErrorMessages(Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    /**
     * Get callback response from paywall/payment provider and proccess it.
     *
     * @Route("/paywall/membership/change")
     */
    public function changeMembershipAction(Request $request)
    {
        $adapter = $this->get('newscoop.paywall.adapter');
        $adapter->setRequest($request);
        $adapterResult = $adapter->proccess();
        $userService = $this->container->get('user');
        $translator = $this->container->get('translator');
        $membershipService = $this->container->get('newscoop_paywall.membership');
        $user = $userService->getCurrentUser();
        $subscriptionService = $this->container->get('subscription.service');
        $errors = array();
        $messages = array();

        if ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user) && $adapterResult['validCode']) {
            $subscription = $subscriptionService->create();
            $subscriptionConfig = $subscriptionService->getOneSubscriptionSpecification($request->request->get('newMembershipType'));
            $subscriptionData = new SubscriptionData(array(
                'userId' => $user,
                'subscriptionId' => $subscriptionConfig->getSubscription(),
                'publicationId' => $subscriptionConfig->getPublication(),
                'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                'days' => $subscriptionConfig->getSubscription()->getRange(),
                'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                'type' => 'P',
                'active' => false
            ), $subscription);

            $subscription = $subscriptionService->update($subscription, $subscriptionData);
            $subscriptionService->save($subscription);

            $currentSubscription = $subscriptionService->getOneByUser($user);
            $toPay = $membershipService->calculatePriceDiff($currentSubscription, $subscription);
            $totalToPay = $toPay . ' ' . $subscription->getCurrency();
            $status = $translator->trans('paywall.msg.upgrade');
            if ($toPay === 0) {
                $status = $translator->trans('paywall.msg.downgrade');
            }

            //send notification to user and admin
            $membershipService->sendEmail($request, $subscription->getSubscription()->getName(), $currentSubscription->getSubscription()->getName(), $totalToPay, $status, true);

            $messages[] = $translator->trans('paywall.msg.process');

            return $this->setTemplateVariables($user, $errors, $messages, null, false, false, true);
        }

        $errors[] = $translator->trans('paywall.msg.cantchange');

        return $this->setTemplateVariables($user, $errors, $messages);
    }

    /**
     * Buy memebrship action
     *
     * @Route("/paywall/membership/buy")
     */
    public function buyAction(Request $request)
    {
        $adapter = $this->get('newscoop.paywall.adapter');
        $adapter->setRequest($request);
        $adapterResult = $adapter->proccess();
        $userService = $this->container->get('user');
        $translator = $this->container->get('translator');
        $membershipService = $this->container->get('newscoop_paywall.membership');
        $user = $userService->getCurrentUser();
        $subscriptionService = $this->container->get('subscription.service');
        $errors = array();
        $messages = array();

        if ($membershipService->isSpam()) {
            $errors[] = $translator->trans('paywall.msg.spammsg');

            return $this->setTemplateVariables($user, $errors, $messages, null, false, true);
        }

        if ($subscriptionService->userHadTrial($user)) {
            $currentSubscription = $subscriptionService->getOneByUser($user);
            $subscription = $subscriptionService->create();
            $subscriptionConfig = $subscriptionService->getOneSubscriptionSpecification($request->request->get('newMembershipType'));
            $subscriptionData = new SubscriptionData(array(
                'userId' => $user,
                'subscriptionId' => $subscriptionConfig->getSubscription(),
                'publicationId' => $subscriptionConfig->getPublication(),
                'toPay' => $subscriptionConfig->getSubscription()->getPrice(),
                'days' => $subscriptionConfig->getSubscription()->getRange(),
                'currency' => $subscriptionConfig->getSubscription()->getCurrency(),
                'type' => 'P',
                'active' => false,
            ), $subscription);

            $subscription = $subscriptionService->update($subscription, $subscriptionData);
            $subscription->setTrial($currentSubscription->getTrial());
            $subscriptionService->save($subscription);

            $totalToPay = $subscription->getToPay() . ' ' . $subscription->getCurrency();
            $status = $translator->trans('paywall.msg.purchasetrialexpired');
            if ($subscriptionService->isTrialActive($user)) {
                $status = $translator->trans('paywall.msg.purchasetrialon');
            }

            // calculates how many days left for trial
            // these result will be added to subscription period
            // left trial days + subscription period
            $leftTrialDays = $membershipService->calculateTrialDiff($currentSubscription->getTrial()->getFinishTrial());
            //send notification to user and admin
            $membershipService->sendEmail($request, $subscription->getSubscription()->getName(), $currentSubscription->getSubscription()->getName(), $totalToPay, $status, true, $leftTrialDays);

            $messages[] = $translator->trans('paywall.msg.process');

            return $this->setTemplateVariables($user, $errors, $messages, null, false, true);
        }
    }
}
