<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Passwordvalid extends Constraint
{
    public string $message = 'Incorrect password ! ';
    public string $emailField;

    public function __construct(
        string $emailField ,
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null 
        
    ) {
        parent::__construct([], $groups, $payload);
        $this->emailField = $emailField;
    }
}
