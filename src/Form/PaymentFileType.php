<?php

namespace App\Form;

use App\Entity\CartFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PaymentFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', VichImageType::Class, [
//                'required' => true,
                'label' => ' ',
                'attr' =>  [
                    'class' => 'hidden',
//                    'style' => 'display:none;'
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CartFile::class,
        ]);
    }
}
