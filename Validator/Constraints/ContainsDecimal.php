<?php

namespace Newscoop\PaywallBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsDecimal extends Constraint
{
    public $message = 'The "%string%" field contains an illegal character: it can only contain numbers.';
    public $entity;
    public $property;

    public function validatedBy()
    {
        return 'decimal_validator';
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}