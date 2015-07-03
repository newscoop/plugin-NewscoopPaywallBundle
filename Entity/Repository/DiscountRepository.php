<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Discount repository.
 */
class DiscountRepository extends EntityRepository
{
    /**
     * Finds active discounts.
     */
    public function findActive()
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->orderBy('d.countBased', 'DESC')
        ;

        return $qb
            ->getQuery()
        ;
    }

    /**
     * Finds count based discounts.
     */
    public function findCountBased()
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->where('d.countBased = true')
            ->orderBy('d.createdAt', 'DESC')
        ;

        return $qb
            ->getQuery()
        ;
    }
}
