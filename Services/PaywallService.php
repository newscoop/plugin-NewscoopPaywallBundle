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

    public function getIssues($id) {
        $issue = $this->em->getRepository('Newscoop\Subscription\Issue')
            ->findBy(array(
                'subscription' => $id,
            ));

        $issues = array();
        foreach ($issue as $i) {
            $issues[] = array(
                'name' => $i->getName(),
                'language' => $i->getLanguage()->getName(),
                'date' => $i->getStartDate(),
                'days' => $i->getDays(),
                'paid' => $i->getPaidDays(),
            );
        }

        return $issues;
    }

    public function getSections($id) {
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
        
        return $sections;
    }

    public function getArticles($id) {
        $article = $this->em->getRepository('Newscoop\Subscription\Article')
            ->findBy(array(
                'subscription' => $id,
            ));

        $articles = array();
        foreach ($article as $a) {
            $article[] = array(
                'name' => $a->getName(),
                'language' => $a->getLanguage()->getName(),
                'date' => $a->getStartDate(),
                'days' => $a->getDays(),
                'paid' => $a->getPaidDays(),
            );
        }
        
        return $articles;
    }
}