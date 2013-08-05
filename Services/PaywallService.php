<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\Subscription\Subscription;
use Newscoop\Subscription\SubscriptionData;
use Newscoop\Services\SubscriptionService;

class PaywallService extends SubscriptionService
{
    /**
     * Remove Subscription by Id
     * @param  integer $id - user subscription id
     * @return void
     */
    public function removeById($id) {
        
        $subscription = $this->em->getRepository('Newscoop\Subscription\Subscription')
            ->findBy(array(
                'id' => $id
            ));
        
        foreach ($subscription as $s) {
            $s->setActive(false);
        }

        $this->em->flush();
    }

    public function getByAll() {
        $subscriptions = $this->em->getRepository('Newscoop\Subscription\Subscription')
            ->findAll();

        $users = array();
        foreach ($subscriptions as $s) {
            $users[] = array(
                'id' => $s->getId(),
                'userid' => $s->getUser()->getId(),
                'username' => $s->getUser()->getUsername(),
                'name' => $s->getUser()->getName(),
                'publication' => $s->getPublicationName(),
                'topay' => $s->getToPay(),
                'currency' => $s->getCurrency(),
                'type' => $s->getType(),
            );
        } 

        return $users;
    }

    public function getBySubscription($id) {
        $section = $this->em->getRepository('Newscoop\Subscription\Section')
            ->findBy(array(
                'subscription' => $id,
            ));
        $sections = array();
        foreach ($section as $s) {
            $sections[] = array(
                'name' => $s->getName(),
                'language' => $s->getLanguage()->getName(),
                'date' => $s->getStartDate(),
                'days' => $s->getDays(),
                'paid' => $s->getPaidDays(),
            );
        }
        
        return array(
            'sections' => $sections,
        );
    }
}