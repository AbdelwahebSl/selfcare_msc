<?php

namespace App\Form;

use App\Entity\Theme;
use App\Entity\Workshop;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

//use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use function Symfony\Component\Form\ChoiceList\label;

class WorkshopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $date =\DateTime::createFromFormat("Y-m-d", date("Y-m-d"));
        $date =  $date->format("Y");
        $builder
            ->add('name', TextType::class,
                array(
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'Name',
                        'class' => 'form-control'
                    ]
                ))
//            ->add('theme',EntityType::class, array(
//
//                'attr' => [
//                    'class' => 'form-control-file'
//                ]
//            ))
           ->add('theme', EntityType::class, [
                // looks for choices from this entity
                'class' => Theme::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'name',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('imageFile', VichImageType::class,
                array(
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control-file'
                    ]
                )
            )
            ->add('description', TextareaType::class, array(
                'required' => true,
                'attr' => [
                    'placeholder' => 'Description',
                    'class' => 'form-control'
                ]
            ))
            ->add('workshopAbstract', TextType::class, array(
                'required' => false,
                'attr' => [
                    'placeholder' => 'Abstract',
                    'class' => 'form-control'
                ]
            ))
            ->add('workshopEstablishment', TextType::class, array(
                'attr' => [
                    'placeholder' => 'Establishment'
                ]
            ))
            ->add('objectiveCount', IntegerType::class, array(
                'required' => true,
                'attr' => [
                    'placeholder' => 'Objectives',
                    'class' => 'form-control'
                ]
            ))
            ->add('workshopDuration', IntegerType::class, array(
                'required' => true,
                'attr' => [
                    'placeholder' => 'Duration',
                    'class' => 'form-control'
                ]
            ))
            ->add('durationUnit', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'HOURS' => 'HOURS',
                    'DAYS' => 'DAYS',
                    'MINUTES' => 'MINUTES',
                ],
                'attr' => [
                    'placeholder' => 'Duration Unit',
                    'class' => 'form-control'
                ]
            ])
            ->add('price', TextType::class, array(
                'required' => true,
                'attr' => [
                    'placeholder' => 'Price',
                    'class' => 'form-control'
                ]
            ))
            ->add('workshopType', ChoiceType::class, [
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
            ->add('workshopOrder', IntegerType::class, array(
                'attr' => [
                    'placeholder' => 'Order',
                    'class' => 'form-control'
                ]
            ))
            ->add('workshopStatus', CheckboxType::class, array(
                'required'=>false,
                'label' => 'Active',
                'attr' => [
                    'class' => 'form-control'
                ]
            ))
            ->add('save', SubmitType::class, array(
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workshop::class,
        ]);
    }
}
