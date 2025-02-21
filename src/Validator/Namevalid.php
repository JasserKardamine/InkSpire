<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Namevalid extends Constraint
{

    public function __construct(
        public string $message ,
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null
        
    ) {
        parent::__construct([], $groups, $payload);
    }
}
