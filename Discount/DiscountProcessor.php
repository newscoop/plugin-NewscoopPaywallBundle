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
use Doctrine\Common\Collections\Criteria;

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
       // ladybug_dump($this->getAllDiscounts());
   //     die;


        foreach ($this->getAllDiscounts() as $discount) {
            if (!$this->isEligibleForDiscount($object, $discount)) {
                continue;
            }

            $eligibleDiscounts[] = $discount;
        }
        //ladybug_dump($eligibleDiscounts);
        //die;
        foreach ($eligibleDiscounts as $discount) {
            $this->container->get('newscoop_paywall.discounts.'.$discount->getType())
                ->applyTo($object, $discount);
        }

        return $object;
    }

    protected function isEligibleForDiscount(DiscountableInterface $object, Discount $discount)
    {
        if ($object instanceof UserSubscription) {
            //ladybug_dump($object->getOrder()->countItems(), $object->hasDiscount($discount), $discount->getValue());

            if ($object->getProlonged()) {
                return $this->isEligibleWhenProlonged($object, $discount);
            }

            /*if ($object->getProlonged()) {

                if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
                    return true;
                }

                if (!$object->hasDiscount($discount)) {
                    return false;
                }

                return true;*/

                // remove count based one
                /*if ($object->getOrder()->countItems() === 1 && $object->hasDiscount($discount) && $discount->getCountBased()) {
                    ladybug_dump('111');
                    $object->removeDiscount($discount);

                    return false;
                }

                if ($object->getOrder()->countItems() > 1 && $object->hasDiscount($discount) && !$discount->getCountBased()) {
                    ladybug_dump('vvv');
                    $object->removeDiscount($discount);

                    return false;
                }

                if (!$object->hasDiscount($discount)) {
                    ladybug_dump($discount->getValue());

                    return true;
                }

                $object->removeDiscount($discount);

                return false;*/
            //}

            /*if (!$object->hasDiscount($discount) && $object->getOrder()->countItems() > 1) {
                ladybug_dump('when no discount and count is > 1');
                //die;
                return true;
            }*/

            /*if (!$object->hasDiscount($discount) && !$discount->getCountBased()) {
                //ladybug_dump('sss');

                return true;
            }*/

            /*if ($object->getOrder()->countItems() == 1 && !$discount->getCountBased()) {
                return true;
            }*/
            $duration = $object->getDuration();

            //$criteria = Criteria::create()->where(Criteria::expr()->in('value', array($duration['value'])));

            //ladybug_dump($object->getDiscounts()->toArray());
            //die;
            if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }

            if (!$object->hasDiscount($discount)) {
                return false;
            }

            if ($object->getOrder()->countItems() > 1 && $discount->getCountBased()) {
                return true;
            }

            //$duration = $object->getDuration();
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
