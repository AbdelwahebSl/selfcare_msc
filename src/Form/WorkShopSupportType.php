<?php

namespace App\Form;

use App\Entity\WorkShopSupport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class WorkShopSupportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('supportDescription')
            ->add('supportType', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Link' => 'Link',
                    'Video' => 'Video',
                    'Image' => 'Image',
                ],
                'attr' => [
                    'placeholder' => 'Support type',
                    'class' => 'form-control'
                ]
            ])
            ->add('supportLink')
            ->add('supportOrder')
            ->add('supportStatus')
            ->add('imageFile', VichImageType::class, [
                'required' => false])
            ->add('workshop')
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkShopSupport::class,
        ]);
    }
}
