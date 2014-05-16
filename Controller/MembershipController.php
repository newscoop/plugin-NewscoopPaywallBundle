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
        $errors = array();
        $messages = array();
        $upgrade = false;
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

        $subscriptionService->isValidTrial($user);
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

                $errors[] = 'Membership expired! Provide valid customer number to activate memberships!';
            //}
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

                        $messages[] = $translator->trans('Successfully changed membership type!');

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
                            //send email here
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

                        $messages[] = $translator->trans('Siccessfully changed membership type!');
                    } else {
                        if (!$adapterResult['status']) {
                            $errors[] = $translator->trans('You have/had already a trial, cant change to other membership!');
                        }
                    }

                    if ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user) && $adapterResult['validCode']) {
                        // if upgrade/downgrade
                        $upgrade = true;
                        $selected = $data['membershipType'];
                        $messages[] = $translator->trans('Are you sure you want to change membership to selected one?');
                    }
                }

                if ($isSubscribed && $adapterResult['validCode'] || ($isSubscribed && !$adapterResult['status'])) {
                    if (array_key_exists('thanks', $adapterResult)) {
                        if ($adapterResult['thanks']) {
                            $messages[] = $translator->trans('Number accepted. Thank you for subscribing!');
                        }
                    } else {
                        $errors[] = $translator->trans('paywall.error.alreadymember');
                    }
                }

                if ($isSubscribed && $adapterResult['status'] && !$adapterResult['validCode'] ||
                    !$isSubscribed && $adapterResult['status'] && !$adapterResult['validCode']) {
                    $errors[] = $translator->trans('Invalid customer number!');
                }

            } else {
                $errors[] = $this->getErrorMessages($form) ?: $translator->trans('paywall.error.fatal');//$this->getErrorMessages($form);
            }
        }

        $userSubscription = $subscriptionService->getOneByUser($user);

        if (!$selected) {
            $selected = !$userSubscription ? null : $userSubscription->getSubscription()->getId();
        }

        $response = new Response();
        $templatesService = $this->get('newscoop.templates.service');
        $smarty = $templatesService->getSmarty();
        $smarty->assign('errors', $errors);
        $smarty->assign('messages', $messages);
        $smarty->assign('code', $user->getAttribute('customer_id') ?: null);
        $smarty->assign('active', $selected);
        $smarty->assign('subscriptions', $subscriptionService->getSubscriptionsConfig());
        $smarty->assign('upgrade', $upgrade);
        $smarty->assign('hadTrial', ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user)));
        $response->setContent($templatesService->fetchTemplate("_views/dashboard_membership.tpl"));
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
        $em = $this->container->get('em');
        $user = $userService->getCurrentUser();
        $subscriptionService = $this->container->get('subscription.service');
        //upgrade
        // dodaj nowa subscrypcje dla usera, ustaw status na nieaktywna
        // poprzednia subskrybscje deaktywuj jesli nowa jest just aktywana (w backendzie)
        // wyslij maila
        try {
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

            return new Response('You need to wait for activating you new subscription');
        }
    } catch (\Exception $e) {ladybug_dump($e->getMessage());die;}
        /*if ($subscriptionService->userHadTrial($user) && !$subscriptionService->isTrialActive($user) && $adapterResult['validCode']) {
            $newSubscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
                ->findOneBy(array(
                    'id' => $request->request->get('newMembershipType')
                ));

            $userSubscription = $subscriptionService->getOneByUser($user);
            $subscriptionData = new SubscriptionData(array(
                'userId' => $user,
                'subscriptionId' => $newSubscription,
                'toPay' => $newSubscription->getPrice(),
                'days' => $newSubscription->getRange(),
                'currency' => $newSubscription->getCurrency(),
                'type' => 'P',
                'active' => 'Y'
                ), $userSubscription);
            $subscriptionService->update($userSubscription, $subscriptionData);
            $em->flush();

            //calculate diffrences between current membership and plan that is going to be switched
            // charge more only when upgrading!

            return new Response('Successfully upgraded/downgraded membership');
        }*/

        return new Response('Cant change membership!');
    }
}
