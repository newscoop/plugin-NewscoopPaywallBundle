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
    public function getByAll() {
        $subscriptions = $this->em->getRepository('Newscoop\Subscription\Subscription')
            ->findAll();

        $subscriptionsArray = array();
        foreach ($subscriptions as $subscription) {
            $subscriptionsArray[] = array(
                'id' => $subscription->getId(),
                'userid' => $subscription->getUser()->getId(),
                'username' => $subscription->getUser()->getUsername(),
                'name' => $subscription->getUser()->getName(),
                'publication' => $subscription->getPublicationName(),
                'topay' => $subscription->getToPay(),
                'currency' => $subscription->getCurrency(),
                'type' => $subscription->getType(),
            );
        } 

        return $subscriptionsArray;
    }

    public function getIssues($id) {
        $issues = $this->em->getRepository('Newscoop\Subscription\Issue')
            ->findBy(array(
                'subscription' => $id,
            ));

        $issuesArray = array();
        foreach ($issues as $issue) {
            $issuesArray[] = array(
                'id' => $issue->getId(),
                'name' => $issue->getName(),
                'language' => $issue->getLanguage()->getName(),
                'date' => $issue->getStartDate(),
                'days' => $issue->getDays(),
                'paid' => $issue->getPaidDays(),
            );
        }

        return $issuesArray;
    }

    public function getSections($id) {
        $sections = $this->em->getRepository('Newscoop\Subscription\Section')
            ->findBy(array(
                'subscription' => $id,
            ));

        $sectionsArray = array();
        foreach ($sections as $section) {
            $sectionsArray[] = array(
                'id' => $section->getId(),
                'name' => $section->getName(),
                'language' => $section->getLanguage()->getName(),
                'date' => $section->getStartDate(),
                'days' => $section->getDays(),
                'paid' => $section->getPaidDays(),
            );
        }

        return $sectionsArray;
    }

    public function getArticles($id) {
        $articles = $this->em->getRepository('Newscoop\Subscription\Article')
            ->findBy(array(
                'subscription' => $id,
            ));

        $articlesArray = array();
        foreach ($articles as $article) {
            $articlesArray[] = array(
                'id' => $article->getId(),
                'name' => $article->getName(),
                'language' => $article->getLanguage()->getName(),
                'date' => $article->getStartDate(),
                'days' => $article->getDays(),
                'paid' => $article->getPaidDays(),
            );
        }
        
        return $articlesArray;
    }
}