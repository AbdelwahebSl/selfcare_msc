<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ThemeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/theme', name: 'app_theme')]
    public function index(): Response
    {
        return $this->render('theme/index.html.twig', [
            'controller_name' => 'ThemeController',
        ]);
    }

    #[Route('/theme/new', name: 'create_theme')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();

        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $theme = $form->getData();
            $theme->setUpdatedAt(new \DateTime());
            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('themes_list');
        }

        return $this->renderForm('theme/new.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/theme/edit/{id}', name: 'edit_theme')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();

        $entityManager = $doctrine->getManager();
        $theme = $entityManager->getRepository(Theme::class)->find($id);

        if (!$theme) {
            throw $this->createNotFoundException(
                'No theme found for id ' . $id
            );
        }

        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $theme = $form->getData();
            $theme->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            return $this->redirectToRoute('themes_list');
        }

        return $this->renderForm('theme/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }


}
