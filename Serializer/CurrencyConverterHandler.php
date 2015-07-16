<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Serializer;

use JMS\Serializer\JsonSerializationVisitor;
use Newscoop\PaywallBundle\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Currency\Converter\CurrencyConverterInterface;

/**
 * Currency converter handler. Handles currency conversion.
 */
class CurrencyConverterHandler
{
    private $converter;
    private $context;

    /**
     * Construct.
     *
     * @param CurrencyConverterInterface $converter
     * @param CurrencyContextInterface   $context
     */
    public function __construct(CurrencyConverterInterface $converter, CurrencyContextInterface $context)
    {
        $this->converter = $converter;
        $this->context = $context;
    }

    public function serializeToJson(JsonSerializationVisitor $visitor, $subscription, $type)
    {
        if (!$subscription) {
            return;
        }

        return $this->converter->convert($subscription->getPrice(), $this->context->getCurrency());
    }
}
