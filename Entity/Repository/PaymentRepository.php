<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Newscoop\PaywallBundle\Entity\Payment;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Payment repository.
 */
class PaymentRepository extends EntityRepository implements RepositoryInterface
{
    public function createNew()
    {
        return new Payment();
    }

    /**
     * Find all available payments.
     */
    public function findAllAvailable()
    {
        $qb = $this
            ->createQueryBuilder('d')
        ;

        return $qb
            ->getQuery()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(array $criteria = null, array $orderBy = null)
    {
    }
}
