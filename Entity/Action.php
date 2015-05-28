<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_discount_action")
 */
class Action
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
     * @ORM\Column(type="string", name="type")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="array", name="configuration")
     *
     * @var array
     */
    protected $configuration = array();

    /**
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="actions")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     *
     * @var Discount
     */
    protected $discount;

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
     * Gets the value of type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param string $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of configuration.
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Sets the value of configuration.
     *
     * @param array $configuration the configuration
     *
     * @return self
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Gets the value of discount.
     *
     * @return Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Sets the value of discount.
     *
     * @param Discount $discount the discount
     *
     * @return self
     */
    public function setDiscount(Discount $discount)
    {
        $this->discount = $discount;

        return $this;
    }
}
