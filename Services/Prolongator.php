<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Doctrine\ORM\EntityManager;
use Newscoop\PaywallBundle\Entity\ProlongableItemInterface;
use Newscoop\PaywallBundle\Entity\Prolongation;

/**
 * Subscription prolongator.
 */
class Prolongator extends AbstractProlongator
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(ProlongableItemInterface $item, $period)
    {
        $duration = $item->getDuration();
        $unitPrice = $item->getToPay() / $duration['value'];
        $total = $unitPrice * $period->getValue();
        $discountTotal = 0;
        if ($period->getDiscount()) {
            // TODO handle not only percentage discounts
            $discountTotal = $total * $period->getDiscount()->getValue();
            $total = $total - $discountTotal;
        }

        $configuration = array(
            'total' => $total,
            'discountTotal' => $discountTotal,
            'period' => array(
                'value' => $period->getValue(),
                'attribute' => $period->getAttribute(),
            ),
            'currency' => $item->getCurrency(),// get currency from the currency context
            // as user can prolong subscription in a diffrent currency when changing locale
        );

        //$prolongation = new Prolongation();

        //$prolongation->setTotal($total);
        //$prolongation->setOrderItem($item);

        //$diffrence = (int) $now->diff($prolongation->getCreatedAt())->format('%m');
        //$months = $period->getValue() + $diffrence;
        //$timeSpan = new \DateInterval('P'.$period->getValue().'M');
        //$prolongation->setPeriod($item->getExpiresAt()->add($timeSpan));

        $this->createProlongation($item, $configuration);
        //$item->addProlongation($prolongation);
    }

    public function prolong(ProlongableItemInterface $item, $period)
    {
        /*$prolongation->setPeriod(array(
            'id' => $period->getId(),
            'value' => $period->getValue(),
            'attribute' => $period->getAttribute(), )
        );*/

         //$diffrence = (int) $now->diff($prolongation->getCreatedAt())->format('%m');
         //$months = $period->getValue() + $diffrence;
        //$timeSpan = new \DateInterval('P'.$period->getValue().'M');
        //$prolongation->setPeriod($item->getExpiresAt()->add($timeSpan));
    }
}
