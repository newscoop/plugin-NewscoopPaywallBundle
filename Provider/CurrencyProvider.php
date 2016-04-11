<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Provider;

use Sylius\Component\Currency\Provider\CurrencyProvider as BaseProvider;
use Newscoop\PaywallBundle\Entity\Repository\CurrencyRepository;

/**
 * Currency Provider class.
 */
class CurrencyProvider extends BaseProvider
{
    /**
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        parent::__construct($currencyRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableCurrencies()
    {
        return $this->currencyRepository
            ->findAllAvailable()
            ->getResult();
    }

    /**
     * Gets the default currency.
     *
     * @return null|Sylius\Component\Currency\Model\CurrencyInterface
     */
    public function getDefaultCurrency()
    {
        return $this->currencyRepository
            ->findOneBy(array(
                'isActive' => true,
                'default' => true,
            ));
    }

    /**
     * Gets the default currency.
     *
     * @return array
     */
    public function getEnabledCurrencies()
    {
        return $this->currencyRepository
            ->findBy(array('isActive' => true));
    }
}
