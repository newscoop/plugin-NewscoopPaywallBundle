<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Gateway repository.
 */
class GatewayRepository extends EntityRepository
{
    /**
     * Finds active gateways/pament methods.
     */
    public function findActive()
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->where('d.isActive = true')
        ;

        return $qb
            ->getQuery()
        ;
    }
}
