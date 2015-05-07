<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.p
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Meta;

use Newscoop\PaywallBundle\Entity\UserSubscription;

/**
 * Meta subscriptions class
 */
class MetaSubscription
{
    public $identifier;
    public $currency;
    public $price;
    public $name;
    public $type;
    public $start_date;
    public $expiration_date;
    public $is_active;
    public $is_valid;
    public $publication;
    public $defined;
    public $expire_in_days;
    private $subscription;

    public function __construct($subscription = null)
    {
        if (!$subscription instanceof UserSubscription) {
            $em = \Zend_Registry::get('container')->getService('em');
            if (!$subscription) {
                return;
            }

            $this->subscription = $em->getReference('Newscoop\PaywallBundle\Entity\UserSubscription', $subscription);
            if (!$this->subscription) {
                $this->subscription = new UserSubscription();
            }
        } else {
            $this->subscription = $subscription;
        }

        $this->identifier = $this->subscription->getId();
        $this->currency = $this->subscription->getCurrency();
        $this->price = $this->subscription->getToPay();
        $this->name = $this->subscription->getSubscription()->getName();
        $this->type = $this->getType();
        $this->start_date = $this->subscription->getCreatedAt();
        $this->expiration_date = $this->subscription->getExpireAt();
        $this->is_active = $this->isActive();
        $this->publication = $this->getPublication();
        if ($this->subscription->getExpireAt()) {
            $this->expire_in_days = $this->subscription->getExpireAt()
                ->diff($this->subscription->getCreatedAt())->format('%a');
        }
    }

    protected function getType()
    {
        $type = $this->subscription->getType();

        return $type == 'T' ? 'trial' : 'paid';
    }

    protected function getStartDate()
    {
        $startDate = null;
        $em = \Zend_Registry::get('container')->getService('em');
        $sections = $em->getRepository("Newscoop\PaywallBundle\Entity\Section")
            ->createQueryBuilder('s')
            ->where('s.subscription = :subscriptionId')
            ->setParameters(array(
                'subscriptionId' => $this->subscription->getId(),
            ))
            ->getQuery()
            ->getResult();

        foreach ($sections as $section) {
            $sectionStartDate = $section->getStartDate();
            if ($sectionStartDate < $startDate || is_null($startDate)) {
                $startDate = $sectionStartDate;
            }
        }

        return $startDate;
    }

    protected function isActive()
    {
        return $this->subscription->isActive();
    }

    protected function getPublication()
    {
        return $this->subscription->getPublicationName();
    }
}
