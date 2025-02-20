<?php
namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class EmailexistValidator extends ConstraintValidator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {

        if (!$constraint instanceof Emailexist) {
            throw new UnexpectedTypeException($constraint, Emailexist::class);
        }

        if (null === $value || '' === $value) {
            return; 
        }

        
        $user = $this->userRepository->findOneBy(['email' => $value]);


        if (!$user) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value) 
                ->addViolation(); 
        }
    }
}
