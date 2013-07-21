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
use Doctrine\Common\Collections\ArrayCollection;
use Newscoop\PaywallBundle\Validator\Constraints as PaywalValidators;

/**
 * Subscriptions entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_subscriptions")
 */
class Subscriptions 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="name")
     * @var string
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Newscoop\PaywallBundle\Entity\Subscription_specification", mappedBy="subscription")
     * @var array
     */
    private $specification;

    /**
     * @ORM\Column(type="text", name="type")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(type="integer", name="ranges")
     * @var int
     */
    private $range;

    /**
     * @PaywalValidators\ContainsDecimal(entity="Newscoop\PaywallBundle\Entity\Subscriptions", property="price")
     * @ORM\Column(type="decimal", name="price")
     * @var decimal
     */
    private $price;

    /**
     * @ORM\Column(type="string", name="currency")
     * @var string
     */
    private $currency;

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
        $this->specification = new ArrayCollection();
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        
        return $name;
    }

    /**
     * Get specification
     *
     * @return array
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }

    public function getRange()
    {
        return $this->range;
    }

    public function setRange($range)
    {
        $this->range = $range;
        
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        
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