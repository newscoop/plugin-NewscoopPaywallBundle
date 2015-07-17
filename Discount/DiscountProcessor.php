<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Entity\UserSubscription;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Newscoop\PaywallBundle\Entity\Discount;

/**
 * Process all discounts for order items.
 */
class DiscountProcessor implements DiscountProcessorInterface
{
    protected $container;
    protected $discounts;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DiscountableInterface $object)
    {
        $eligibleDiscounts = array();
        foreach ($this->getAllDiscounts() as $discount) {
            if (!$this->isEligibleForDiscount($object, $discount)) {
                continue;
            }

            $eligibleDiscounts[] = $discount;
        }

        foreach ($eligibleDiscounts as $discount) {
            $this->container->get('newscoop_paywall.discounts.'.$discount->getType())
                ->applyTo($object, $discount);
        }

        return $object;
    }

    protected function isEligibleForDiscount(DiscountableInterface $object, Discount $discount)
    {
        if ($object instanceof UserSubscription) {
            if ($object->getProlonged()) {
                return $this->isEligibleWhenProlonged($object, $discount);
            }

            $duration = $object->getDuration();
            if ($object->getOrder()->countItems() == 1 &&
                !$discount->getCountBased() &&
                $object->hasDiscount($discount)
            ) {
                return true;
            }

            if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }

            if (!$object->hasDiscount($discount)) {
                return false;
            }

            if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }

            if ($object->getOrder()->countItems() > 1 && $duration['value'] > 1) {
                return true;
            }
        }

        if ($object instanceof OrderInterface) {
            if ($object->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }
        }

        return false;
    }

    public function isEligibleWhenProlonged(DiscountableInterface $object, Discount $discount)
    {
        if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
            return true;
        }

        if (!$object->hasDiscount($discount)) {
            return false;
        }

        return true;
    }

    private function getAllDiscounts()
    {
        if (null === $this->discounts) {
            $this->discounts = $this->getDiscountRepository()
                ->findActive()
                ->getResult();
        }

        return $this->discounts;
    }

    private function getDiscountRepository()
    {
        return $this->container->get('em')->getRepository('Newscoop\PaywallBundle\Entity\Discount');
    }
}