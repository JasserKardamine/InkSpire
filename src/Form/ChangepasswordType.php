<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ChangepasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentpassword', PasswordType::class, [
                'label' => 'Current Password',
                'empty_data' => '',
                'attr' => ['placeholder' => 'Current password'],
                'constraints' => [
                    new Assert\NotBlank(['message' => '* Current password is required'])
                ]
            ])
            ->add('newpassword', PasswordType::class, [
                'label' => 'New Password',
                'empty_data' => '',
                'attr' => ['placeholder' => 'New password'],
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
            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, 
        ]);
    }
}
