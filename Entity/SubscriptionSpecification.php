<?php

/**
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Subscription Specification entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\SubscriptionSpecificationRepository")
 * @ORM\Table(name="plugin_paywall_subscription_specification")
 */
class SubscriptionSpecification
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Subscription", inversedBy="specification")
     * @ORM\JoinColumn(name="subscription_id", referencedColumnName="id")
     *
     * @var \Newscoop\PaywallBundle\Entity\Subscription
     */
    protected $subscription;

    /**
     * @ORM\Column(type="integer", name="publication")
     *
     * @var int
     */
    protected $publication;

    /**
     * @ORM\Column(type="integer", name="issue", nullable=true)
     *
     * @var int
     */
    protected $issue;

    /**
     * @ORM\Column(type="integer", name="section", nullable=true)
     *
     * @var int
     */
    protected $section;

    /**
     * @ORM\Column(type="integer", name="article", nullable=true)
     *
     * @var int
     */
    protected $article;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var string
     */
    protected $created_at;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $is_active;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
        $this->subscription = new ArrayCollection();
    }

    /**
     * Get specification id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get subscription.
     *
     * @return Newscoop\PaywallBundle\Entity\Subscriptions
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Get subscription.
     *
     * @param Subscription $subscription
     *
     * @return Subscription
     */
    public function setSubscription(Subscription $subscription)
    {
        $this->subscription = $subscription;

        return $subscription;
    }

    /**
     * Get publication id.
     *
     * @return int
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Set publication id.
     *
     * @param int $publication
     *
     * @return int
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get issue id.
     *
     * @return int
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Set issue id.
     *
     * @param int $issue
     *
     * @return int
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get section id.
     *
     * @return int
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set section id.
     *
     * @param int $section
     *
     * @return int
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get article id.
     *
     * @return int
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set article id.
     *
     * @param int $article
     *
     * @return int
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get specification status.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set specification status.
     *
     * @param bool $is_active
     *
     * @return bool
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Get specification create date.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set specification create date.
     *
     * @param datetime $created_at
     *
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }
}
