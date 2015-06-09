<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Importer;

use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Currency\Importer\AbstractImporter as BaseImporter;

abstract class AbstractImporter extends BaseImporter
{
    protected $baseCurrency;

    /**
     * {@inheritdoc}
     */
    protected function updateOrCreate(array $managedCurrencies, $code, $rate)
    {
        if (!empty($managedCurrencies)) {
            foreach ($managedCurrencies as $currency) {
                if ($currency->getCode() !== $this->baseCurrency) {
                    $currency->setDefault(false);
                } else {
                    $currency->setDefault(true);
                }

                if ($code === $currency->getCode()) {
                    $currency->setExchangeRate($rate);
                    $currency->setUpdatedAt(new \DateTime());

                    return;
                }
            }
        } else {
            /** @var $currency CurrencyInterface */
            $currency = $this->repository->createNew();
            $currency->setCode($code);
            $currency->setExchangeRate($rate);
            if ($currency->getCode() === $this->baseCurrency) {
                $currency->setDefault(true);
            }

            $this->manager->persist($currency);
        }
    }
}
