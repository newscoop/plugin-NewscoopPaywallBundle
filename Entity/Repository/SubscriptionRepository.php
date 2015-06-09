<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Newscoop\ListResult;

/**
 * Subscription repository.
 */
class SubscriptionRepository extends EntityRepository
{
    /**
     * Gets all available subscriptions by criteria.
     *
     * @param SubscriptionCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria($criteria)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('s', 'r', 'd')
            ->andWhere('s.is_active = :is_active')
            ->leftJoin('s.ranges', 'r')
            ->leftJoin('r.discount', 'd')
            ->setParameter('is_active', true);

        if ($criteria->name) {
            $qb->andWhere('s.name = :name')
                ->setParameter('name', $criteria->name);
        }

        if ($criteria->currency) {
            $qb->andWhere('s.currency = :currency')
                ->setParameter('currency', $criteria->currency);
        }

        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('s.'.$key.' = :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $metadata = $this->getClassMetadata();
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 's.'.$key;
            }

            $qb->orderBy($key, $order);
        }

        $list = new ListResult();
        $countBuilder = clone $qb;
        $list->count = (int) $countBuilder->select('COUNT(s)')->getQuery()->getSingleScalarResult();
        $list->items = $qb->getQuery();

        return $list;
    }
}
