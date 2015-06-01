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
            ->orderBy('d.id', 'DESC')
        ;

        return $qb
            ->getQuery()
        ;
    }
}
