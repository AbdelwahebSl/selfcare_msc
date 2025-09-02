<?php

namespace App\Form;

use App\Entity\SelfcareUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //<input type="mail" class="form-control" placeholder="Email" aria-label="Email" aria-describedby="basic-addon1">
//                <input type="password" class="form-control" placeholder="Password"
// aria-label="Password" aria-describedby="basic-addon1">
        // <input type="text" class="form-control" placeholder="Full Name" aria-label="text"
        //aria-describedby="basic-addon1">
        $builder
            //->add('fullName')
            ->add('fullName',
                TextType::class,
                array(
                    'empty_data' => 'Full Name',
                    'attr' => ['class' => 'form-control'],
                    'label' => false,
                )
            )
            ->add('email',
                EmailType::class,
                array(
                    'attr' => ['class' => 'form-control'],
                    'label' => false,
                )
            )
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => false,
                'attr' => ['autocomplete' => 'new-password','class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])/* ->add('phoneNumber')
            ->add('userAddress')
            ->add('country')
            ->add('establishment')*/
            /* ->add('agreeTerms', CheckboxType::class, [
                 'mapped' => false,
                 'constraints' => [
                     new IsTrue([
                         'message' => 'You should agree to our terms.',
                     ]),
                 ],
             ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SelfcareUser::class,
        ]);
    }
}
