<?php

namespace App\Form;

use App\Entity\ProfilePic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfilePicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('imageFile', VichImageType::Class, [
//               // 'required' => true,
//                'label' => 'file_upload',
//                'label_attr'=>['class'=> 'material-icons'],
//                'attr' =>  [
//                    'class' => 'd-none hidden',
//                    'style' => 'media:(min-width: 768px);'
//                ]
//            ])
            ->add('imageFile', VichFileType::class,
                array(
                    'allow_delete' => false,
                    'download_label' => false,
                    'required' => false,
                    'label' => false,
                    'attr' => [
//                     'style'=>'visibility:hidden;',
                    ]
                )
            )
            ->add('save', SubmitType::class, array(
                'attr' => [
                    'style'=>'visibility:hidden;'
                ]
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfilePic::class,
        ]);
    }
}
