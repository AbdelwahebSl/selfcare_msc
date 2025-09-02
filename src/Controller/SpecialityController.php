<?php

namespace App\Controller;

use App\Entity\Speciality;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\SpecialityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

class SpecialityController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/speciality', name: 'app_speciality')]
    public function index(): Response
    {
        return $this->render('speciality/index.html.twig');
    }

    #[Route('/speciality/new', name: 'create_speciality')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();

        $speciality = new Speciality();
        $form = $this->createForm(SpecialityType::class, $speciality);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $speciality = $form->getData();
            $speciality->setUpdatedAt(new \DateTime());
            //saving the speciality to the database
            $entityManager->persist($speciality);
            $entityManager->flush();

            return $this->redirectToRoute('specialities_list');
        }

        return $this->renderForm('speciality/new.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }


    #[Route('/speciality/edit/{id}', name: 'edit_speciality')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');


        $user = $this->security->getUser();

        $entityManager = $doctrine->getManager();
        $speciality = $entityManager->getRepository(Speciality::class)->find($id);

        if (!$speciality) {
            throw $this->createNotFoundException(
                'No speciality found for id ' . $id
            );
        }

        $form = $this->createForm(SpecialityType::class, $speciality);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $speciality = $form->getData();
            $speciality->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('specialities_list');
        }

        return $this->renderForm('speciality/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/speciality/{id}', name: 'speciality_show')]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $speciality = $doctrine->getRepository(Speciality::class)->find($id);
        if (!$speciality) {
            throw $this->createNotFoundException(
                'No speciality found for id ' . $id
            );
        }
        return $this->render('speciality/show.html.twig', ['speciality' => $speciality]);
    }

}
