<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Discount entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\DiscountRepository")
 * @ORM\Table(name="plugin_paywall_discount")
 */
class Discount implements DiscountInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name", length=100)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="description")
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Duration", mappedBy="discount")
     *
     * @var ArrayCollection
     */
    protected $durations;

    /**
     * @ORM\Column(type="string", name="type")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="float", scale=3, name="value")
     *
     * @var float
     */
    protected $value;

    /**
     * @ORM\Column(type="boolean", name="count_based")
     *
     * @var bool
     */
    protected $countBased = false;

    /**
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->durations = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param \DateTime $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the value of createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the value of countBased.
     *
     * @return bool
     */
    public function isCountBased()
    {
        return $this->countBased;
    }

    /**
     * Sets the value of countBased.
     *
     * @param bool $countBased the count based
     *
     * @return self
     */
    public function setCountBased($countBased)
    {
        $this->countBased = $countBased;

        return $this;
    }

    /**
     * Gets the value of durations.
     *
     * @return ArrayCollection
     */
    public function getDurations()
    {
        return $this->durations;
    }

    /**
     * Sets the value of durations.
     *
     * @param ArrayCollection $durations the durations
     *
     * @return self
     */
    public function setDurations(ArrayCollection $durations)
    {
        $this->durations = $durations;

        return $this;
    }
}
