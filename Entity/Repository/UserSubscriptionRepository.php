<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Newscoop\PaywallBundle\Criteria\SubscriptionCriteria;
use Newscoop\ListResult;

/**
 * Subscription repository
 */
class UserSubscriptionRepository extends EntityRepository
{
    /**
     * Get list for given criteria
     *
     * @param SubscriptionCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(SubscriptionCriteria $criteria)
    {
        $qb = $this->createQueryBuilder('s');
        $list = new ListResult();

        $qb->select('s', 'p', 'u', 'ss')
            ->leftJoin('s.publication', 'p')
            ->leftJoin('s.user', 'u')
            ->leftJoin('s.subscription', 'ss');

        foreach ($criteria->orderBy as $key => $value) {
            $qb->orderBy($key, $value == '-1' ? 'desc' : 'asc');
        }

        $countQb = clone $qb;
        $list->count = (int) $countQb->select('COUNT(DISTINCT u)')->getQuery()->getSingleScalarResult();

        if (!empty($criteria->query)) {
            $qb->where($qb->expr()->orX("(u.username LIKE :query)", "(p.name LIKE :query)"));
            $qb->setParameter('query', '%' . trim($criteria->query, '%') . '%');
        }

        if ($criteria->firstResult != 0) {
            $qb->setFirstResult($criteria->firstResult);
        }

        if ($criteria->maxResults != 0) {
            $qb->setMaxResults($criteria->maxResults);
        }

        $list->items = $qb->getQuery()->getArrayResult();

        return $list;
    }

    /**
     * Get subscriptions count for given criteria
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
     * Find by user
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
}
