<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class GooglesignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('password', PasswordType::class, [
            'label' => 'Password',
            'empty_data' => '',
            'attr' => ['placeholder' => 'Password'],
            'constraints' => [
                new Assert\Length([
                    'min' => 8,
                    'max' => 100,
                    'minMessage' => '* invalid password length ! ',
                    'maxMessage' => '* invalid password length !'
                ])
            ]
        ])
        ->add('confirmpassword', PasswordType::class, [
            'label' => 'Confirm Password',
            'empty_data' => '',
            'attr' => ['placeholder' => 'Confirm password'],
            'constraints' => [
                new Assert\Length([
                    'min' => 8,
                    'max' => 100,
                    'minMessage' => '* invalid password length !',
                    'maxMessage' => '* invalid password length !'
                ])
            ]
        ])
        ->add('signup', SubmitType::class, [
            'label' => 'Sign up '
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
