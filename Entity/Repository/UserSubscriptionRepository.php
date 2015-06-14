<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;
use Newscoop\ListResult;
use Newscoop\PaywallBundle\Notifications\Emails;
use Newscoop\PaywallBundle\Entity\UserSubscription;

/**
 * Subscription repository.
 */
class UserSubscriptionRepository extends EntityRepository
{
    /**
     * Get list for given criteria.
     *
     * @param SubscriptionCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(SubscriptionCriteria $criteria, $returnQuery = false)
    {
        $qb = $this->createQueryBuilder('s');
        $list = new ListResult();

        $qb->select('s', 'p', 'u', 'ss')
            ->leftJoin('s.publication', 'p')
            ->leftJoin('s.user', 'u')
            ->leftJoin('s.subscription', 'ss');

        if ($criteria->order) {
            $qb->where('s.order = :order')
                ->setParameter('order', $criteria->order);
        }

        if ($criteria->user) {
            $qb->andWhere('s.user = :user')
                ->setParameter('user', $criteria->user);
        }

        foreach ($criteria->orderBy as $key => $value) {
            switch ($key) {
                case '0':
                    $qb->orderBy('u.username', $value);
                    break;
                case '1':
                    $qb->orderBy('p.name', $value);
                    break;
                case '2':
                    $qb->orderBy('s.toPay', $value);
                    break;
                case '3':
                    $qb->orderBy('s.currency', $value);
                    break;
                case '4':
                    $qb->orderBy('s.active', $value);
                    break;
                case '5':
                    $qb->orderBy('s.type', $value);
                    break;
            }
        }

        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('s.'.$key.' = :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $countQb = clone $qb;
        $list->count = (int) $countQb->select('COUNT(DISTINCT u)')->getQuery()->getSingleScalarResult();

        if (!empty($criteria->query)) {
            $qb->andWhere($qb->expr()->orX('(u.username LIKE :query)', '(p.name LIKE :query)'));
            $qb->setParameter('query', '%'.trim($criteria->query, '%').'%');
        }

        if ($criteria->firstResult != 0) {
            $qb->setFirstResult($criteria->firstResult);
        }

        if ($criteria->maxResults != 0) {
            $qb->setMaxResults($criteria->maxResults);
        }

        if ($returnQuery) {
            return $qb->getQuery();
        }

        $list->items = $qb->getQuery()->getArrayResult();

        return $list;
    }

    /**
     * Get subscriptions count for given criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('count(u)')
            ->from($this->getEntityName(), 'u');

        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $queryBuilder->andWhere("u.$property = :$property");
            }
        }

        $query = $queryBuilder->getQuery();
        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $query->setParameter($property, $value);
            }
        }

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Find by user.
     *
     * @param Newscoop\Entity\User|int $user
     *
     * @return array
     */
    public function findByUser($user)
    {
        if (empty($user)) {
            return array();
        }

        return $this->findBy(array(
            'user' => is_numeric($user) ? $user : $user->getId(),
        ), array('id' => 'desc'), 1000);
    }

    /**
     * Gets expiring subscriptions count.
     *
     * @param \DateTime $now    Date time
     * @param int       $notify Notify type
     * @param int       $days   Days amount, when to send notification
     *
     * @return Doctrine\ORM\Query
     */
    public function getExpiringSubscriptionsCount($now, $notify, $days = 7)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select('count(s)')
            ->where("DATE_SUB(s.expire_at, :days, 'DAY') < :now")
            ->andWhere('s.active = :status');

        switch ($notify) {
            case Emails::NOTIFY_LEVEL_ONE:
                $qb->andWhere($qb->expr()->isNull('s.notifySentLevelOne'));
                break;
            case Emails::NOTIFY_LEVEL_TWO:
                $qb->andWhere($qb->expr()->isNotNull('s.notifySentLevelOne'));
                $qb->andWhere($qb->expr()->isNull('s.notifySentLevelTwo'));
                break;
            default:
                break;
        }

        $qb->setParameters(array(
            'status' => 'Y',
            'now' => $now,
            'days' => $days,
        ))
        ->orderBy('s.created_at', 'desc');

        return $qb->getQuery();
    }

    /**
     * Gets expiration subscriptions query.
     *
     * @param int       $offset First result
     * @param int       $batch  Max results
     * @param \DateTime $now    Date time
     * @param int       $notify Notify type
     * @param int       $days   Days amount, when to send notification
     *
     * @return Doctrine\ORM\Query
     */
    public function getExpiringSubscriptions($offset, $batch, $now, $notify, $days = 7)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where("DATE_SUB(s.expire_at, :days, 'DAY') < :now")
            ->andWhere('s.active = :status');

        switch ($notify) {
            case Emails::NOTIFY_LEVEL_ONE:
                $qb->andWhere($qb->expr()->isNull('s.notifySentLevelOne'));
                break;
            case Emails::NOTIFY_LEVEL_TWO:
                $qb->andWhere($qb->expr()->isNotNull('s.notifySentLevelOne'));
                $qb->andWhere($qb->expr()->isNull('s.notifySentLevelTwo'));
                break;
            default:
                break;
        }

        $qb
            ->setParameters(array(
                'status' => 'Y',
                'now' => $now,
                'days' => $days,
            ))
            ->orderBy('s.created_at', 'desc')
            ->setFirstResult($offset)
            ->setMaxResults($batch);

        return $qb->getQuery();
    }

    public function getValidSubscriptionsBy($userId)
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->select('s', 'ss', 'sp')
            ->where('s.user = :user')
            ->andWhere("s.active = 'Y'")
            ->andWhere('s.expire_at >= :now')
            ->join('s.subscription', 'ss')
            ->join('ss.specification', 'sp')
            ->setParameter('user', $userId)
            ->setParameter('now', new \DateTime('now'));

        return $qb->getQuery();
    }

    /**
     * Finds item in order.
     */
    public function checkExistanceInOrder(UserSubscription $item)
    {
        $qb = $this
            ->createQueryBuilder('i')
            ->select('count(i)')
            ->where('i.subscription = :subscriptionId')
            ->andWhere('i.order = :orderId')
            ->setParameters(array(
                'subscriptionId' => $item->getSubscription()->getId(),
                'orderId' => $item->getOrder()->getId(),
            ));

        return (int) $qb
            ->getQuery()->getSingleScalarResult()
        ;
    }

    public function getOrderItemBy($id, $user, $period = null)
    {
        $qb = $this->createQueryBuilder('i');
        $query = $qb
            ->where('i.user = :user')
            ->andWhere('i.subscription = :id')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('i.active', $qb->expr()->literal('Y')),
                $qb->expr()->eq('i.active', $qb->expr()->literal('N'))
            ))
            //->andWhere('i.prolonged = true') /// to add?
            //->andWhere($qb->expr()->eq('i.duration', $qb->expr()->literal(serialize($period))))
            ->setParameters(array(
                'user' => $user,
                'id' => $id,
            ))
            ->setMaxResults(1)//
            ->orderBy('i.created_at', 'desc')//
            ->getQuery();

        return $query
            ->getOneOrNullResult()
        ;
    }
}
