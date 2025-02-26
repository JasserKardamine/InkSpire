<?php

namespace App\Form;

use App\Entity\Artwork;
use App\Entity\Auction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class AuctionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label',TextType::class)
            ->add('startDate', DateTimeType::class,[
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class,[
                'widget' => 'single_text',
            ])
            ->add('startPrice',NumberType::class)
            ->add('artwork', EntityType::class, [
                'label' => false ,
                'class' => Artwork::class,
                'choices' => $options['artworks'],
                'multiple' => false,
                'expanded' => true,
                'choice_label' => 'id',
                'choice_attr' => function ($artwork) {
                    return [
                        'data-image' => $artwork->getPicture(),
                    ];
                },
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Auction::class,
            'artworks' => [],
        ]);
    }
}
