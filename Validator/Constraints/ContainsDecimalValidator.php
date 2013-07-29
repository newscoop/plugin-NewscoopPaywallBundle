<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsDecimalValidator extends ConstraintValidator
{
    /**
     * Add violations to the validator's context property
     *
     * @param $string $value
     * @param Symfony\Component\Validator\Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_numeric($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}