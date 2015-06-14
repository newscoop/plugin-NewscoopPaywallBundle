<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Converter;

use Sylius\Component\Currency\Converter\CurrencyConverterInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Currency\Converter\UnavailableCurrencyException;

/**
 * Currency Converter class.
 */
class CurrencyConverter implements CurrencyConverterInterface
{
    protected $currencyRepository;
    protected $cache;

    /**
     * @param RepositoryInterface $currencyRepository
     */
    public function __construct(RepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($value, $code)
    {
        $currency = $this->getCurrency($code);

        if (null === $currency) {
            throw new UnavailableCurrencyException($code);
        }

        return (int) round($value * $currency->getExchangeRate());
    }

    private function getCurrency($code)
    {
        if (isset($this->cache[$code])) {
            return $this->cache[$code];
        }

        return $this->cache[$code] = $this->currencyRepository
            ->findOneBy(array(
                'code' => $code,
                'isActive' => true,
            ));
    }
}
