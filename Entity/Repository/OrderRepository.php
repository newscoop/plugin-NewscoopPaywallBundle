<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Order repository.
 */
class OrderRepository extends EntityRepository
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
}
