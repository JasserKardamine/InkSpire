<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('picture' , FileType::class , [
                'label' => 'Profile Picture' , 
                'mapped' => false,
            ])  
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'empty_data' => ''
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'empty_data' => ''
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => ''
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'empty_data' => ''
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
