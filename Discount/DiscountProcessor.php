<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Discount;

use Newscoop\PaywallBundle\Entity\UserSubscription;
use Newscoop\PaywallBundle\Entity\OrderInterface;
use Newscoop\PaywallBundle\Entity\Discount as DiscountEntity;

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

        $this->processCountBasedDiscounts($object);
        foreach ($eligibleDiscounts as $discount) {
            $this->container->get('newscoop_paywall.discounts.'.$discount->getType())
                ->applyTo($object, $discount);
        }

        return $object;
    }

    /**
     * Checks if the order or the order item is
     * eligible for discount.
     *
     * @param DiscountableInterface $object
     * @param DiscountEntity        $discount
     *
     * @return bool
     */
    protected function isEligibleForDiscount(DiscountableInterface $object, DiscountEntity $discount)
    {
        if ($object instanceof UserSubscription) {
            return $this->processOrderItem($object, $discount);
        }

        if ($object instanceof OrderInterface) {
            if ($object->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }
        }

        return false;
    }

    private function processOrderItem(DiscountableInterface $object, DiscountEntity $discount)
    {
        $selectedDiscount = $object->getDiscount();
        if ($object->getProlonged() && $selectedDiscount['id'] === $discount->getId()) {
            return true;
        }

        if ($object->getOrder()->countItems() == 1 &&
                !$discount->getCountBased() &&
                $object->hasDiscount($discount)
            ) {
            return true;
        }

        if (!$discount->getCountBased() && $selectedDiscount['id'] === $discount->getId()) {
            return true;
        }

        return false;
    }

    private function processCountBasedDiscounts(DiscountableInterface $object)
    {
        foreach ($this->getAllDiscounts() as $discount) {
            if ($discount->getCountBased()) {
                $discountTempValue = $discount->getValue();
                $discount->setValue($discountTempValue * ($object->getOrder()->getItems()->count() - 1));

                $this->container->get('newscoop_paywall.discounts.'.$discount->getType())
                    ->applyTo($object, $discount);

                $discount->setValue($discountTempValue);
            }
        }
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
