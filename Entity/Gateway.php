<?php

/**
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gateway entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\GatewayRepository")
 * @ORM\Table(name="plugin_paywall_gateways")
 */
class Gateway
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
     * @ORM\Column(type="string", name="name")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="text", name="value")
     *
     * @var string
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $isActive = true;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return string
     */
    public function setName($name)
    {
        $this->name = $name;

        return $name;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return string
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Set status.
     *
     * @param bool $isActive
     */
    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get creation date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set creation date.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
