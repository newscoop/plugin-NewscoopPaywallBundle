<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Subscription Specification repository.
 */
class SubscriptionSpecificationRepository extends EntityRepository
{
    public function findSpecification($articleNumber, $publicationId)
    {
        $queryBuilder = $this->createQueryBuilder('ss')
                ->join('ss.subscription', 's')
                ->where('ss.article = :article')
                ->andWhere('ss.publication = :publication')
                ->andWhere('s.is_active = true')
                ->setParameters(array(
                    'article' => $articleNumber,
                    'publication' => $publicationId,
                ));

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
