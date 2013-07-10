<?php

namespace Newscoop\PaywallBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsDecimalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!is_numeric($value)) {
            $this->context->addViolation($constraint->message, array('%string%' => $value));
        }
    }
}