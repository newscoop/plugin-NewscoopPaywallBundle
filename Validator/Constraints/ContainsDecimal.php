<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsDecimal extends Constraint
{
    public $message = 'step1.form.error.price.decimal.custom';
    public $entity;
    public $property;

    /**
     * Use ContainsDecimalValidator class when actually performing the validation
     *
     * @return Newscoop\PaywallBundle\Validator\Constraints\ContainsDecimalValidator
     */
    public function validatedBy()
    {
        return 'decimal_validator';
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}