<?php

namespace App\Form;

use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;


class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('themeStatus')
            ->add('themeOrder')
            ->add('themeType', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Professional' => 1,
                    'Student' => 2,
                ],
                'attr' => [
                    'placeholder' => 'Type',
                    'class' => 'form-control'
                ]
            ])
            ->add('imageFile', VichImageType::class, array(
                'required' => false,))
//            ->add('speciality')
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }
}
