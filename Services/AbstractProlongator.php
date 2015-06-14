<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Entity\Prolongation;
use Newscoop\PaywallBundle\Entity\ProlongableItemInterface;

/**
 * Abstract prolongator.
 */
abstract class AbstractProlongator implements ProlongatorInterface
{
    /**
     * Creates prolongation to a given order item.
     *
     * @param ProlongableItemInterface $item          [description]
     * @param array                    $configuration [description]
     *
     * @return [type] [description]
     */
    public function createProlongation(ProlongableItemInterface $item, array $configuration = array())
    {
        $prolongation = new Prolongation();
        $prolongation->setDiscountTotal(-$configuration['discountTotal']);
        $prolongation->setCurrency($configuration['currency']);
        $prolongation->setTotal($configuration['total']);
        $prolongation->setPeriod($configuration['period']);
        $prolongation->setOrderItem($item);
        $item->addProlongation($prolongation);
    }
}
