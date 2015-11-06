<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Newscoop\ListResult;

/**
 * Subscription repository.
 */
class SubscriptionRepository extends TranslationRepository
{
    /**
     * Gets all available subscriptions by criteria.
     *
     * @param SubscriptionCriteria $criteria
     * @param bool                 $returnQuery
     *
     * @return ListResult|Doctrine\ORM\Query
     */
    public function getListByCriteria($criteria, $returnQuery = false)
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

        if ($criteria->type) {
            $qb->andWhere('s.type = :type')
                ->setParameter('type', $criteria->type);
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
        $query = $this->setTranslatableHints($qb->getQuery(), $criteria->locale);
        if ($returnQuery) {
            return $query;
        }

        $list->count = (int) $countBuilder->select('COUNT(s)')->getQuery()->getSingleScalarResult();
        $list->items = $query;

        return $list;
    }

    /**
     * Gets active Subscriptions by the specification and type.
     *
     * @param string $locale Current language code (e.g. "en")
     * @param array  $specs  Subscription specification
     *                       (e.g. publication id, section number etc.)
     *
     * @return array
     */
    public function findActiveBy($locale = null, $specs = array())
    {
        $queryBuilder = $this
            ->createQueryBuilder('s')
            ->select('s', 'r')
            ->leftJoin('s.ranges', 'r')
            ->join('s.specification', 'sp')
            ->where('s.is_active = true');

        if (!empty($specs)) {
            foreach ($specs as $key => $value) {
                if (!$value) {
                    $queryBuilder
                        ->andWhere('sp.'.$key.' IS NULL');
                } else {
                    $queryBuilder
                        ->andWhere('sp.'.$key.' = :'.$key)
                        ->setParameter($key, $value);
                }
            }
        }

        $query = $queryBuilder->getQuery();

        return $this->setTranslatableHints($query, $locale)
            ->getArrayResult();
    }

    public function findActiveOneBy($id, $locale = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->where('s.id = :id')
            ->andWhere('s.is_active = true')
            ->setParameter('id', $id)
            ->getQuery()
        ;

        return $this->setTranslatableHints($query, $locale)
            ->getOneOrNullResult()
        ;
    }

    public function getReference($id)
    {
        return $this->getEntityManager()->getReference($this->getEntityName(), $id);
    }
}
