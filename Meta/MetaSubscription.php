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
        $this->is_valid = $this->isValid();
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

    protected function getExpirationDate()
    {
        $expirationDate = null;
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
            $sectionExpDate = $section->getExpirationDate();
            if ($sectionExpDate > $expirationDate) {
                $expirationDate = $sectionExpDate;
            }
        }

        return $expirationDate;
    }

    protected function isActive()
    {
        return $this->subscription->isActive();
    }

    protected function isValid()
    {
        $expirationDate = $this->getExpirationDate();
        $today = new \Date(time());

        return (int) ($this->isActive() && $expirationDate >= $today->getDate());
    }

    protected function getPublication()
    {
        return $this->subscription->getPublicationName();
    }

    public function has_section($sectionNumber)
    {
        $today = new \Date(time());
        $em = \Zend_Registry::get('container')->getService('em');
        $section = $em->getRepository("Newscoop\PaywallBundle\Entity\Section")->findOneBy(array(
            'subscription' => $this->subscription->getId(),
            'sectionNumber' => $sectionNumber,
        ));

        if ($section && $section->getExpirationDate() >= $today->getDate()) {
            return (int) true;
        }

        $currentLanguageNumber = \CampTemplate::singleton()->context()->language->number;
        $section = $em->getRepository("Newscoop\PaywallBundle\Entity\Section")->findOneBy(array(
            'subscription' => $this->subscription->getId(),
            'sectionNumber' => $sectionNumber,
            'language' => $currentLanguageNumber,
        ));

        return (int) ($section && $section->getExpirationDate() >= $today->getDate());
    }

    public function has_article($articleNumber)
    {
        $container = \Zend_Registry::get('container');
        $today = new \Date(time());
        $currentLanguageNumber = \CampTemplate::singleton()->context()->language->number;
        $subscriptionId = $this->subscription->getId();

        $subscriptionArticle = $container->getService('em')
            ->getRepository('Newscoop\PaywallBundle\Entity\Article')
            ->findOneBy(array(
                'subscription' => $subscriptionId,
                'articleNumber' => $articleNumber,
                'language' => $currentLanguageNumber,
            ));

        if ($subscriptionArticle) {
            if ($subscriptionArticle->getExpirationDate() >= $today->getDate()) {
                return (int) true;
            }
        }

        return (int) false;
    }

    public function has_issue($issueNumber)
    {
        $container = \Zend_Registry::get('container');
        $today = new \Date(time());
        $currentLanguageNumber = \CampTemplate::singleton()->context()->language->number;
        $subscriptionId = $this->subscription->getId();

        $subscriptionIssue = $container->getService('em')
            ->getRepository('Newscoop\PaywallBundle\Entity\Issue')
            ->findOneBy(array(
                'subscription' => $subscriptionId,
                'issueNumber' => $issueNumber,
                'language' => $currentLanguageNumber,
            ));

        if ($subscriptionIssue) {
            if ($subscriptionIssue->getExpirationDate() >= $today->getDate()) {
                return (int) true;
            }
        }

        return (int) false;
    }
}
