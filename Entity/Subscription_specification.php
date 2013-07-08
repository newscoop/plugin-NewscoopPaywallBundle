<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Subscription_specification entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_subscription_specification")
 */
class Subscription_specification 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Subscriptions")
     * @var Newscoop\PaywallBundle\Entity\Subscriptions
     */
    private $subscription_id;

    /**
     * @ORM\Column(type="integer", name="publication")
     * @var int
     */
    private $publication;

    /**
     * @ORM\Column(type="integer", name="issue")
     * @var int
     */
    private $issue;

    /**
     * @ORM\Column(type="integer", name="section")
     * @var int
     */
    private $section;

    /**
     * @ORM\Column(type="integer", name="article")
     * @var int
     */
    private $article;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    private $created_at;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     * @var boolean
     */
    private $is_active;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
        $this->subscription_id = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getSubscriptionId()
    {
        return $this->subscription_id;
    }

    public function setSubscriptionId($subscription_id)
    {
        $this->subscription_id = $subscription_id;
        
        return $subscription_id;
    }

    public function getPublication()
    {
        return $this->publication;
    }

    public function setPublication($publication)
    {
        $this->publication = $publication;
        
        return $this;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function setIssue($issue)
    {
        $this->issue = $issue;
        
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
        
        return $this;
    }

    public function getArticle()
    {
        return $this->article;
    }

    public function setArticle($article)
    {
        $this->article = $article;
        
        return $this;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;
        
        return $this;
    }
}

