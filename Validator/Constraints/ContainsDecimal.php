<?php

namespace Newscoop\PaywallBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsDecimal extends Constraint
{
    public $message = 'The %string% field contains an illegal character: it can only contain numbers.';
    
    public function validatedBy()
    {
        return 'decimal_validator';
    }
}