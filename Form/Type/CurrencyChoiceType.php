<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Form\Type;

use Sylius\Component\Currency\Provider\CurrencyProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CurrencyChoiceType extends AbstractType
{
    /**
     * @var CurrencyProviderInterface
     */
    protected $currencyProvider;

    public function __construct(CurrencyProviderInterface $currencyProvider)
    {
        $this->currencyProvider = $currencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = null;

        foreach ($this->currencyProvider->getEnabledCurrencies() as $currency) {
            $choices[$currency->getCode()] = sprintf('%s - %s', $currency->getCode(), $currency->getName());
        }

        $resolver->setDefaults(array(
            'choices' => $choices,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paywall_currency_choice';
    }
}
