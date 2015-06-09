<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Newscoop\PaywallBundle\Validator\Constraints as PaywallValidators;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Symfony\Component\Intl\Intl;

/**
 * Currency entity.
 *
 * @ORM\Entity(repositoryClass="Newscoop\PaywallBundle\Entity\Repository\CurrencyRepository")
 * @ORM\Table(name="plugin_paywall_currency")
 */
class Currency implements CurrencyInterface
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
     * @ORM\Column(type="string", name="currency_code")
     *
     * @var string
     */
    protected $code;

    /**
     * @PaywallValidators\ContainsDecimal(entity="Currency", property="exchangeRate")
     * @ORM\Column(type="decimal", name="exchange_rate", precision=10, scale=5)
     *
     * @var decimal
     */
    protected $exchangeRate;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $isActive = true;

    /**
     * @ORM\Column(type="boolean", name="is_default")
     *
     * @var bool
     */
    protected $default = false;

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
     * {@inheritdoc}
     */
    public function getName()
    {
        return Intl::getCurrencyBundle()->getCurrencyName($this->code);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * {@inheritdoc}
     */
    public function setExchangeRate($rate)
    {
        $this->exchangeRate = $rate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->isActive;
    }

    public function getIsActive()
    {
        return $this->isEnabled();
    }

    public function setIsActive($isActive)
    {
        $this->setEnabled($isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->isActive = (Boolean) $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the value of default.
     *
     * @return bool
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Sets the value of default.
     *
     * @param bool $default the default
     *
     * @return self
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }
}
