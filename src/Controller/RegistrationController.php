<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\SelfcareUser;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;

class RegistrationController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // if user is already logged in, don't display the login page again
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('workshops_list');
        }

        if ($this->security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('my_learning');
        }

        $country = $entityManager->getRepository(Country::class)->findAll();
        $countries = [];
        foreach ($country as $item) {
             $countries[]= [
                 'id'=>$item->getId(),
                 'nationality'=>$item->getNationality(),
                 'name'=>$item->getName(),
                 'countryCode'=>$item->getCountryCode()
             ];
        }

        if ($request->getMethod() == 'POST' ) {
            $user = new SelfcareUser();
            $userExist = $entityManager->getRepository(SelfcareUser::class)
                ->findOneBy(['email' => $request->get('_email')]);
            if ($userExist) {
                $this->addFlash('warning', 'There is already an account with this email.');
                return $this->redirectToRoute('app_register');
            }


            $fullName = $request->get('name') . ' ' . $request->get('lastname');
            $user->setName($request->get('name'));
            $user->setLastName($request->get('lastname'));
            $user->setFullName($fullName);
            $user->setEmail($request->get('_email'));
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get('_plainPassword')
                )
            );
            $pays = $entityManager->getRepository(Country::class)->findOneBy(['id'=>$request->get('pays')]);
            $user->setCountry($pays->getNationality());
            $user->setLevel($request->get('etude'));
            $user->setPhoneNumber('+'.$pays->getCountryCode().$request->get('tel'));
            $user->setRoles(["ROLE_PROFESSIONAL"]);

            $entityManager->persist($user);



            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('my_profile');

        }

        /*if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('theme_show_all');
        }*/

        return $this->render('registration/register.html.twig', [
                'country' => $countries
            ]

        );
    }


}
