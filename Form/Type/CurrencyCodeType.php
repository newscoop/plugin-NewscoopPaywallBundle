<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Form\Type;

use Sylius\Component\Currency\Provider\CurrencyProviderInterface;
use Sylius\Bundle\CurrencyBundle\Form\Type\CurrencyCodeChoiceType;

class CurrencyCodeType extends CurrencyCodeChoiceType
{
    /**
     * @param CurrencyProviderInterface $currencyProvider
     */
    public function __construct(CurrencyProviderInterface $currencyProvider)
    {
        parent::__construct($currencyProvider);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paywall_currency_code';
    }
}
