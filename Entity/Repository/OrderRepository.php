<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

/**
 * Order repository.
 */
class OrderRepository extends TranslationRepository
{
    /**
     * Finds all orders.
     */
    public function findOrders()
    {
        $qb = $this
            ->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
        ;

        return $qb
            ->getQuery()
        ;
    }

    public function findSingleBy($id, $locale)
    {
        $query = $this
            ->createQueryBuilder('o')
            ->select('o', 'i', 's')
            ->join('o.items', 'i')
            ->join('i.subscription', 's')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
        ;

        return $this->setTranslatableHints($query, $locale)
            ->getSingleResult()
        ;
    }

    /**
     * Finds all orders by user.
     */
    public function findByUser($user)
    {
        $qb = $this
            ->createQueryBuilder('o')
            ->where('o.user = :user')
            ->setParameter('user', $user)
        ;

        return $qb
            ->getQuery()
        ;
    }
}
