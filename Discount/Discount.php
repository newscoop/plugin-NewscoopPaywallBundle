<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Entity\Modification;
use Newscoop\PaywallBundle\Entity\DiscountInterface;

/**
 * Abstract discount class.
 */
abstract class Discount implements DiscountTypeInterface
{
    /**
     * @param DiscountInterface $discount
     *
     * @return DiscountInterface
     */
    public function createModification(DiscountInterface $discount)
    {
        $modification = new Modification();
        $modification->setLabel('discount');
        $modification->setDescription($discount->getDescription());
        $modification->setModificationOriginId($discount->getId());
        $modification->setModificationType(get_class($discount));

        return $modification;
    }
}
