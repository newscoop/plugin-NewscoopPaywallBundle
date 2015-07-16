<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Newscoop\PaywallBundle\Entity\Currency;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;

/**
 * Currency repository.
 */
class CurrencyRepository extends EntityRepository implements RepositoryInterface
{
    /**
     * Finds active currencies.
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

    /**
     * Finds the default currency.
     *
     * @return null|Sylius\Component\Currency\Model\CurrencyInterface
     */
    public function findDefaultOne()
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->where('d.isActive = true')
            ->andWhere('d.default = true')
        ;

        return $qb
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Find all available currencies.
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

    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        return new Currency();
    }

    /**
     * Checks if currency exists.
     *
     * @param CurrencyInterface $currency
     *
     * @return Doctrine\ORM\Query
     */
    public function checkIfExists(CurrencyInterface $currency)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('count(d)')
            ->where('d.code = :code')
            ->andWhere('d.id <> :id')
            ->setParameters(array(
                'code' => $currency->getCode(),
                'id' => $currency->getId(),
            ))
        ;

        return $qb
            ->getQuery();
    }
}
