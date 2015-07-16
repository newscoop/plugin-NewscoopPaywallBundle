<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Serializer;

use JMS\Serializer\JsonSerializationVisitor;
use Newscoop\PaywallBundle\Currency\Context\CurrencyContextInterface;

/**
 * Currency handler. Handles currency conversion.
 */
class CurrencyContextHandler
{
    private $context;

    /**
     * Construct.
     *
     * @param CurrencyConverter $converter
     */
    public function __construct(CurrencyContextInterface $context)
    {
        $this->context = $context;
    }

    public function serializeToJson(JsonSerializationVisitor $visitor, $object, $type)
    {
        return $this->context->getCurrency();
    }
}
