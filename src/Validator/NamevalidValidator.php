<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NamevalidValidator extends ConstraintValidator
{   
    public function validate(mixed $value, Constraint $constraint ): void
    {
        if (!$value || !$constraint instanceof Namevalid) {
            return;
        }

        // Ensure no spaces, only letters & numbers, and not entirely numbers
        if (!preg_match('/^(?!\d+$)[a-zA-Z]+$/', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
