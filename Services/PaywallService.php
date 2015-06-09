<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Subscription\SubscriptionData;
use Newscoop\PaywallBundle\Entity\UserSubscription;
use Newscoop\PaywallBundle\Entity\Subscriptions;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;
use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Entity\Duration;

/**
 * PaywallService manages user's subscriptions.
 */
class PaywallService
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function filterRanges(Subscriptions $subscription, $periodId)
    {
        $ranges = $subscription->getRanges()->filter(function (Duration $duration) use ($periodId) {
            return $duration->getId() == $periodId;
        });

        return $ranges->first();
    }

    /**
     * Gets all user's subscriptions by criteria.
     *
     * @return array
     */
    public function getListByCriteria(SubscriptionCriteria $criteria)
    {
        return $this->getRepository()->getListByCriteria($criteria);
    }

    /**
     * Gets all available subscriptions by criteria.
     *
     * @return array
     */
    public function getSubscriptionsByCriteria(SubscriptionCriteria $criteria)
    {
        return $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->getListByCriteria($criteria);
    }

    /**
     * Gets all user subscriptions by criteria.
     *
     * @return array
     */
    public function getUserSubscriptionsByCriteria(SubscriptionCriteria $criteria, $returnQuery = false)
    {
        return $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->getListByCriteria($criteria, $returnQuery);
    }

    /**
     * Count subscriptions by given criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria)
    {
        return $this->getRepository()->countBy($criteria);
    }

    /**
     * Gets user's subscriptions repository.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription');
    }

    /**
     * Gets user's subscriptions for issues by given Id.
     *
     * @param int $id Subscription Id to search for
     *
     * @return array
     */
    public function getIssues($id)
    {
        $issues = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Issue')
            ->findBy(array(
                'subscription' => $id,
            ));

        $issuesArray = array();
        foreach ($issues as $issue) {
            $issueName = $this->em->getRepository('Newscoop\Entity\Issue')->findOneByNumber($issue->getIssueNumber())->getName();

            $issuesArray[] = array(
                'id' => $issue->getId(),
                'name' => $issueName,
                'language' => $issue->getLanguage()->getName(),
                'date' => $issue->getStartDate(),
                'days' => $issue->getDays(),
                'paid' => $issue->getPaidDays(),
            );
        }

        return $issuesArray;
    }

    /**
     * Gets user's subscriptions for sections by given Id.
     *
     * @param int $id Subscription Id to search for
     *
     * @return array
     */
    public function getSections($id)
    {
        $sections = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Section')
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
     * Gets user's subscriptions for articles by given Id.
     *
     * @param int $id Subscription Id to search for
     *
     * @return array
     */
    public function getArticles($id)
    {
        $articles = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Article')
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
     * Gets currently added user's Sections by given language Id and subscription Id.
     *
     * @param int $language       Language Id to search for
     * @param int $subscriptionId Subscription Id to search for
     *
     * @return array
     */
    public function getSectionsByLanguageAndId($language, $subscriptionId)
    {
        $sections = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Section')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $sections;
    }

    /**
     * Checks if user had trial.
     *
     * @param Newscoop\Entity\User $user User
     *
     * @return bool
     */
    public function userHadTrial($user)
    {
        $qb = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->createQueryBuilder('u');

        $qb->select('count(u.id)')
            ->where('u.user = :user')
            ->andWhere($qb->expr()->isNotNull('u.trial'))
            ->setParameter('user', $user);

        $userTrials = (int) $qb->getQuery()->getSingleScalarResult();

        return $userTrials > 1 ? true : false;
    }

    /**
     * Checks if trial is valid.
     *
     * @param Newscoop\Entity\User $user User
     *
     * @return bool
     */
    public function validateTrial($user)
    {
        $trial = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Trial')
            ->findOneBy(array(
                'user' => $user,
                'is_active' => true,
        ));

        if ($trial) {
            $datetime = new \DateTime('now');
            //if trial expired

            if ($trial->getFinishTrial() >= $datetime) {
                return true;
            }

            // deactivate trial
            $trial->setIsActive(false);
            $this->em->flush();

            return false;
        }

        return false;
    }

    /**
     * Checks if trial is active.
     *
     * @param Newscoop\Entity\User $user User
     *
     * @return bool
     */
    public function isTrialActive($user)
    {
        $trial = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Trial')
            ->findOneBy(array(
                'user' => $user,
        ));

        if ($trial) {
            return $trial->getIsActive();
        }

        return false;
    }

    /**
     * Deactivates trial.
     *
     * @param Newscoop\Entity\User $user User
     *
     * @return bool
     */
    public function deactivateTrial($user)
    {
        $trial = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Trial')
            ->findOneBy(array(
                'user' => $user,
                'is_active' => true,
        ));

        if ($trial) {
            $trial->setIsActive(false);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Gets all available sections by given language Id.
     *
     * @param int $language Language Id to search for
     *
     * @return array
     */
    public function getSectionsByLanguageId($languageId)
    {
        $sections = $this->em->getRepository('Newscoop\Entity\Section')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $sections;
    }

    /**
     * Gets currently added user's Issues by given language Id and subscription Id.
     *
     * @param int $language        Language Id to search for
     * @param int $subscription_id Subscription Id to search for
     *
     * @return array
     */
    public function getIssuesByLanguageAndId($language, $subscription_id)
    {
        $issues = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Issue')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $issues;
    }

    /**
     * Gets all available Issues by given language Id.
     *
     * @param int $language Language Id to search for
     *
     * @return array
     */
    public function getIssuesByLanguageId($language_id)
    {
        $issues = $this->em->getRepository('Newscoop\Entity\Issue')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $issues;
    }

    /**
     * Gets currently added user's Articles by given language Id and subscription Id.
     *
     * @param int $language        Language Id to search for
     * @param int $subscription_id Subscription Id to search for
     *
     * @return array
     */
    public function getArticlesByLanguageAndId($language, $subscription_id)
    {
        $articles = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Article')
            ->findBy(array(
                'language' => $language,
                'subscription' => $subscription_id,
        ));

        return $articles;
    }

    /**
     * Gets all available Articles by given language Id.
     *
     * @param int $language Language Id to search for
     *
     * @return array
     */
    public function getArticlesByLanguageId($language_id)
    {
        $articles = $this->em->getRepository('Newscoop\Entity\Article')
            ->findBy(array(
                'language' => $language_id,
            ));

        return $articles;
    }

    /**
     * Gets subscription details by given subscription Id.
     *
     * @param int $subscriptionId Subscription Id to search for
     *
     * @return array
     */
    public function getSubscriptionDetails($subscriptionId)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->createQueryBuilder('s')
            ->select('s.type', 's.duration', 's.price', 's.currency', 'i.publication')
            ->innerJoin('s.specification', 'i', 'WITH', 'i.subscription = :id')
            ->where('s.id = :id AND s.is_active = true')
            ->setParameter('id', $subscriptionId)
            ->getQuery()
            ->getArrayResult();

        return $subscription;
    }

    /**
     * Gets one defined subscription by given subscription Id.
     *
     * @param int $subscriptionId Subscription Id to search for
     *
     * @return entity object
     */
    public function getOneSubscriptionById($subscriptionId)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findOneBy(array(
                'id' => $subscriptionId,
            ));

        return $subscription;
    }

    /**
     * Activates Subscription by Id and returns its instance.
     *
     * @param int $id User subscription id
     *
     * @return UserSubscription
     */
    public function activateById($id)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->findOneBy(array(
                'id' => $id,
            ));

        if ($subscription) {
            $subscription->setActive(true);
            $subscription->setType('P');
            if (!$subscription->getExpireAt()) {
                $subscription->setExpireAt($this->getExpirationDate($subscription));
            }

            $this->em->flush();
        }

        return $subscription;
    }

    /**
     * Gets user subscription expiration date.
     *
     * @param UserSubscription $userSubscription User subscription
     *
     * @return DateTime
     */
    public function getExpirationDate(UserSubscription $userSubscription)
    {
        $now = new \DateTime('now');
        $createdAt = $userSubscription->getCreatedAt();
        // diffrence in days between subscription create date
        // and actual activation date
        $startDate = $createdAt ?: $now;
        $duration = $userSubscription->getDuration();
        $value = $duration['value'];
        $attribute = $duration['attribute'];
        $timeSpan = null;
        switch ($attribute) {
            case Duration::MONTHS:
                $diffrence = (int) $now->diff($createdAt)->format('%m');
                $months = $value + $diffrence;
                $timeSpan = new \DateInterval('P'.$months.'M');
                break;
            case Duration::DAYS:
                $daysDiffrence = (int) $now->diff($createdAt)->format('%a');
                $days = $value + $daysDiffrence;
                $timeSpan = new \DateInterval('P'.$days.'D');
                break;
        }

        return $startDate->add($timeSpan);
    }

    /**
     * Gets Subscription configuration(details) by given Subscription Id.
     *
     * @param int $subscriptionId Subscription id
     *
     * @return entity object
     */
    public function getOneSubscriptionSpecification($subscriptionId)
    {
        $subscriptionSpec = $this->em->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
            ->findOneBy(array(
                'subscription' => $subscriptionId,
            ));

        return $subscriptionSpec;
    }

    /**
     * Gets active Subscriptions.
     *
     * @return array
     */
    public function getSubscriptionsConfig()
    {
        $subscriptions = $this->em->getRepository('Newscoop\PaywallBundle\Entity\Subscriptions')
            ->findBy(array('is_active' => true));

        return $subscriptions;
    }

    /**
     * Checks if Subscription by given User Id and Subscription Id exists.
     *
     * @param int    $userId         User id
     * @param int    $subscriptionId Subscription id
     * @param string $active         Active on inactive subscription
     *
     * @return array
     */
    public function getOneByUserAndSubscription($userId, $subscriptionId, $active = 'Y')
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->findOneBy(array(
                'user' => $userId,
                'subscription' => $subscriptionId,
                'active' => $active,
            ));

        if ($subscription) {
            return $subscription;
        }

        return;
    }

    /**
     * Get one user subscription by user.
     *
     * @param Newscoop\Entity\User|int $user User or user id
     *
     * @return UserSubscription|null
     */
    public function getOneByUser($user)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->findOneBy(array(
                'user' => $user,
                'active' => 'Y',
            ));

        if ($subscription) {
            return $subscription;
        }

        return;
    }

    /**
     * Gets all sections diffrent from already added user's sections by given language
     * and publication.
     *
     * @param int $languageId    Language Id
     * @param int $publicationId Publication Id
     *
     * @return array
     */
    public function getDiffrentSectionsByLanguage($languageId, $publicationId)
    {
        $sections = $this->em->getRepository('Newscoop\Entity\Section')
            ->createQueryBuilder('s')
            ->select('s.number', 's.name')
            ->where('s.language != :id AND s.publication = :publicationId')
            ->setParameters(array(
                'id' => $languageId,
                'publicationId' => $publicationId,
            ))
            ->getQuery()
            ->getArrayResult();

        return $sections;
    }

    /**
     * Gets all issues diffrent from already added user's issues by given language
     * and publication.
     *
     * @param int $languageId    Language Id
     * @param int $publicationId Publication Id
     *
     * @return array
     */
    public function getDiffrentIssuesByLanguage($languageId, $publicationId)
    {
        $issues = $this->em->getRepository('Newscoop\Entity\Issue')
            ->createQueryBuilder('i')
            ->select('i.number', 'i.name')
            ->where('i.language != :id AND i.publication = :publicationId')
            ->setParameters(array(
                'id' => $languageId,
                'publicationId' => $publicationId,
            ))
            ->getQuery()
            ->getArrayResult();

        return $issues;
    }

    /**
     * Gets all articles diffrent from already added user's articles by given language
     * and publication.
     *
     * @param int $languageId    Language Id
     * @param int $publicationId Publication Id
     *
     * @return array
     */
    public function getDiffrentArticlesByLanguage($languageId, $publicationId)
    {
        $articles = $this->em->getRepository('Newscoop\Entity\Article')
            ->createQueryBuilder('i')
            ->select('i.number', 'i.name')
            ->where('i.language != :id AND i.publication = :publicationId')
            ->setParameters(array(
                'id' => $languageId,
                'publicationId' => $publicationId,
            ))
            ->getQuery()
            ->getArrayResult();

        return $articles;
    }

    /**
     * Update Subscription according to SubscritionData class.
     *
     * @param UserSubscription $subscription
     * @param SubscriptionData $data
     *
     * @return Subscription
     */
    public function update(UserSubscription $subscription, SubscriptionData $data)
    {
        $subscription = $this->apply($subscription, $data);

        return $subscription;
    }

    private function apply(UserSubscription $subscription, SubscriptionData $data)
    {
        if ($data->userId) {
            $user = $this->em->getRepository('Newscoop\Entity\User')->getOneActiveUser($data->userId, false)->getOneOrNullResult();
            if ($user) {
                $subscription->setUser($user);
            }
        }

        if ($data->publicationId) {
            $publication = $this->em->getRepository('Newscoop\Entity\Publication')->findOneBy(array('id' => $data->publicationId));
            if ($publication) {
                $subscription->setPublication($publication);
            }
        }

        if ($data->toPay) {
            $subscription->setToPay($data->toPay);
        }

        if (!empty($data->duration)) {
            $subscription->setDuration($data->duration);
        }

        if (!empty($data->discount)) {
            $subscription->setDiscount($data->discount);
        }

        if ($data->subscriptionId) {
            $subscription->setSubscription($data->subscriptionId);
        }

        if ($data->currency) {
            $subscription->setCurrency($data->currency);
        }

        if ($data->active) {
            $subscription->setActive($data->active);
        }

        if ($data->type) {
            $subscription->setType($data->type);
        }

        return $subscription;
    }

    public function save(UserSubscription $subscription)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($subscription);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            // Rollback
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * Deactivates Subscription by Id and returns its instance.
     *
     * @param int $id - user subscription id
     *
     * @return UserSubscription
     */
    public function deactivateById($id)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->findOneBy(array(
                'id' => $id,
            ));

        if ($subscription) {
            $subscription->setActive(false);
            $subscription->setType('T');
            $this->em->flush();
        }

        return $subscription;
    }

    /**
     * Removes Subscription by Id and returns its instance.
     *
     * @param int $id User subscription id
     *
     * @return UserSubscription
     */
    public function deleteById($id)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
            ->findOneBy(array(
                'id' => $id,
            ));

        if ($subscription) {
            $this->em->remove($subscription);
            $this->em->flush();
        }

        return $subscription;
    }

    public function getOneById($id)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')->findOneBy(array(
            'id' => $id,
        ));

        return $subscription;
    }

    public function getUserSubscriptionBySubscriptionId($id)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')->findOneBy(array(
            'subscription' => $id,
        ));

        return $subscription;
    }

    public function getOneByUserAndPublication($userId, $publicationId)
    {
        $subscription = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')->findOneBy(array(
            'user' => $userId,
            'publication' => $publicationId,
        ));

        return $subscription;
    }

    public function create()
    {
        $subscription = new UserSubscription();

        return $subscription;
    }

    public function getArticleRepository()
    {
        return $this->em->getRepository('Newscoop\Entity\Article');
    }

    public function getSectionRepository()
    {
        return $this->em->getRepository('Newscoop\Entity\Section');
    }

    public function getLanguageRepository()
    {
        return $this->em->getRepository('Newscoop\Entity\Language');
    }

    public function getIssueRepository()
    {
        return $this->em->getRepository('Newscoop\Entity\Issue');
    }
}
