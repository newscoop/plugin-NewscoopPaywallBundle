<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Meta;

/**
 * Meta subscriptions class.
 */
class MetaSubscriptions
{
    protected $subscriptions;

    public function __construct($publicationId, $userId)
    {
        $this->subscriptions = $this->getSubscriptions(\CampTemplate::singleton()->context()->publication->identifier, \CampTemplate::singleton()->context()->user->identifier);

        if (count($this->subscriptions) == 0) {
            $this->subscriptions = array();
        }
    }

    /**
     * Get all subscriptions by given publication id and user id.
     *
     * @param int $publicationId Publication id
     * @param int $userId        User id
     *
     * @return array Returns array of subscriptions' ids
     */
    protected function getSubscriptions($publicationId, $userId)
    {
        $em = \Zend_Registry::get('container')->getService('em');

        $subscriptions = $em->getRepository("Newscoop\PaywallBundle\Entity\UserSubscription")
            ->createQueryBuilder('s')
            ->select('s.id')
            ->where('s.publication = :publicationId')
            ->andWhere('s.user = :userId')
            ->setParameters(array(
                'publicationId' => $publicationId,
                'userId' => $userId,
            ))
            ->orderBy('s.created_at', 'asc')
            ->getQuery()
            ->getArrayResult();

        return $subscriptions;
    }

    public function has_section($sectionNumber)
    {
        foreach ($this->subscriptions as $subscription) {
            $subscription = new MetaSubscription($subscription['id']);
            if ($subscription->has_section($sectionNumber) && $subscription->is_active) {
                return $subscription;
            }
        }

        return false;
    }

    public function has_article($articleNumber)
    {
        foreach ($this->subscriptions as $subscription) {
            $subscription = new MetaSubscription($subscription['id']);
            if ($subscription->has_article($articleNumber) && $subscription->is_active) {
                return $subscription;
            }
        }

        return false;
    }

    public function has_issue($issueNumber)
    {
        foreach ($this->subscriptions as $subscription) {
            $subscription = new MetaSubscription($subscription['id']);
            if ($subscription->has_issue($issueNumber) && $subscription->is_active) {
                return $subscription;
            }
        }

        return false;
    }
}
