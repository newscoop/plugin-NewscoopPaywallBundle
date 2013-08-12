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

/**
 * PaywallService manages user's subscriptions
 */
class PaywallService extends SubscriptionService
{
    /**
     * Gets all user's subscriptions
     *
     * @return array
     */
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
                'active' => $subscription->isActive(),
            );
        } 

        return $subscriptionsArray;
    }

    /**
     * Gets user's subscriptions for issues by given Id
     *
     * @param integer $id Subscription Id to search for
     *
     * @return array
     */
    public function getIssues($id) {
        $issues = $this->em->getRepository('Newscoop\Subscription\Issue')
            ->findBy(array(
                'subscription' => $id,
            ));

        $issuesArray = array();
        foreach ($issues as $issue) {
            $issuesArray[] = array(
                'id' => $issue->getId(),
                'name' => $issue->getIssue()->getName(),
                'language' => $issue->getLanguage()->getName(),
                'date' => $issue->getStartDate(),
                'days' => $issue->getDays(),
                'paid' => $issue->getPaidDays(),
            );
        }

        return $issuesArray;
    }

    /**
     * Gets user's subscriptions for sections by given Id
     *
     * @param integer $id Subscription Id to search for
     *
     * @return array
     */
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

    /**
     * Gets user's subscriptions for articles by given Id
     *
     * @param integer $id Subscription Id to search for
     *
     * @return array
     */
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

    /**
     * Gets currently added user's Sections by given language Id and subscription Id
     *
     * @param integer $language        Language Id to search for
     * @param integer $subscription_id Subscription Id to search for
     *
     * @return array
     */
    public function getSectionsByLanguageAndId($language, $subscription_id) {

        $sections = $this->em->getRepository('Newscoop\Subscription\Section')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $sections;
    }

    /**
     * Gets all available sections by given language Id
     *
     * @param integer $language Language Id to search for
     *
     * @return array
     */
    public function getSectionsByLanguageId($language_id) {

        $sections = $this->em->getRepository('Newscoop\Entity\Section')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $sections;
    }

    /**
     * Gets currently added user's Issues by given language Id and subscription Id
     *
     * @param integer $language        Language Id to search for
     * @param integer $subscription_id Subscription Id to search for
     *
     * @return array
     */
    public function getIssuesByLanguageAndId($language, $subscription_id) {

        $issues = $this->em->getRepository('Newscoop\Subscription\Issue')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $issues;
    }

    /**
     * Gets all available Issues by given language Id
     *
     * @param integer $language Language Id to search for
     *
     * @return array
     */
    public function getIssuesByLanguageId($language_id) {

        $issues = $this->em->getRepository('Newscoop\Entity\Issue')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $issues;
    }

    /**
     * Gets currently added user's Articles by given language Id and subscription Id
     *
     * @param integer $language        Language Id to search for
     * @param integer $subscription_id Subscription Id to search for
     *
     * @return array
     */
    public function getArticlesByLanguageAndId($language, $subscription_id) {

        $articles = $this->em->getRepository('Newscoop\Subscription\Article')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $articles;
    }

    /**
     * Gets all available Articles by given language Id
     *
     * @param integer $language Language Id to search for
     *
     * @return array
     */
    public function getArticlesByLanguageId($language_id) {

        $articles = $this->em->getRepository('Newscoop\Entity\Article')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $articles;
    }

    /**
     * Gets subscription details by given subscription Id
     *
     * @param integer $subscriptionId Subscription Id to search for
     *
     * @return array
     */
    public function getSubscriptionDetails($subscriptionId) {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->createQueryBuilder('s')
            ->select('s.type', 's.range', 's.price', 's.currency', 'i.publication')
            ->innerJoin('s.specification', 'i', 'WITH', 'i.subscription = :id')
            ->where('s.id = :id AND s.is_active = true')
            ->setParameter('id', $subscriptionId)
            ->getQuery()
            ->getArrayResult();

        return $subscription;
    }

    /**
     * Gets one defined subscription by given subscription Id
     *
     * @param integer $subscriptionId Subscription Id to search for
     *
     * @return entity object
     */
    public function getOneSubscriptionById($subscriptionId) {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneBy(array(
                'id' => $subscriptionId,
            ));

        return $subscription;
    }

    /**
     * Activates Subscription by Id
     *
     * @param  integer $id User subscription id
     *
     * @return void
     */
    public function activateById($id) {
        
        $subscription = $this->em->getRepository('Newscoop\Subscription\Subscription')
            ->findOneBy(array(
                'id' => $id
            ));
            
        if ($subscription) {
            $subscription->setActive(true);
            $this->em->flush();
        }
    }

    /**
     * Gets Subscription configuration(details) by given Subscription Id
     *
     * @param  integer $subscriptionId Subscription id
     *
     * @return entity object
     */
    public function getSubscriptionsConfig($subscriptionId) {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscription_specification')
            ->findOneBy(array(
                'subscription' => $subscriptionId,
            ));

        return $subscription;
    }
}