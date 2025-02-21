<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PasswordvalidValidator extends ConstraintValidator
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Passwordvalid) {
            throw new UnexpectedTypeException($constraint, Passwordvalid::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $object = $this->context->getObject(); 
        
        $email = $object->getEmail() ?? null;


        if (!$email) {
            return ;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]); 

        if ( strlen($value) > 6 && $user &&  !$this->passwordHasher->isPasswordValid($user, $value)) {

            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
