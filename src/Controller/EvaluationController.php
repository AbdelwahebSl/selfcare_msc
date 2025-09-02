<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Workshop;
use App\Entity\WorkshopCart;
use App\Entity\WorkShopObjectives;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class EvaluationController extends AbstractController
{
    private $requestStack;
    private $security;
    private $doctrineManager;

    public function __construct(RequestStack $requestStack, Security $security, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->doctrineManager = $doctrine;
    }


    #[Route('workshop-content/{id}/evaluation', name: 'app_evaluation_cardio')]
    public function index(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){
            return $this->render('evaluation/cardio/fr_cardio-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/cardio/cardio-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }


    #[Route('workshop-content/{id}/evaluation/slow-sequence', name: 'app_evaluation_slow_sequence')]
    public function evalSlowSeq(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
       if ($locale=='fr'){
           return $this->render('evaluation/slow_sequence/fr_slow-seq-quiz.html.twig', [
               'workshop' => $workshopCart->getWorkshop(),
               'id_workshopCart'=>$workshopCart->getId(),
               'objectives' => $objectives,
               'totalRate5' => $totalRate5,
               'percentPerValueRate'=>$percentPerValueRate,
               'recomendedWorkshop'=>$recomendedWorkshop,
               'comments'=>$comments
           ]);
       }else{
           return $this->render('evaluation/slow_sequence/slow-seq-quiz.html.twig', [
               'workshop' => $workshopCart->getWorkshop(),
               'id_workshopCart'=>$workshopCart->getId(),
               'objectives' => $objectives,
               'totalRate5' => $totalRate5,
               'percentPerValueRate'=>$percentPerValueRate,
               'recomendedWorkshop'=>$recomendedWorkshop,
               'comments'=>$comments
           ]);
       }

    }


    #[Route('workshop-content/{id}/evaluation/nasogastric', name: 'app_evaluation_nasogastric_tub')]
    public function nasogastricTub(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){
            return $this->render('evaluation/nasogastric/fr_nasogastric-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/nasogastric/nasogastric-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }


    #[Route('workshop-content/{id}/evaluation/peripheral-pva', name: 'app_peripheral_vein_pva')]
    public function peripheralPVA(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){
            return $this->render('evaluation/peripheral/fr_peripheral-pva-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/peripheral/peripheral-pva-quiz.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }

    #[Route('workshop-content/{id}/peripheral-pva/answers', name: 'app_peripheral_vein_pva_response')]
    public function peripheralPVAResponse(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){
            return $this->render('evaluation/peripheral/fr_peripheral-pva-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);

        }else{
            return $this->render('evaluation/peripheral/peripheral-pva-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }





    #[Route('workshop-content/{id}/evaluation/answers', name: 'app_evaluation_cardio_response')]
    public function responseQuizCardio(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

//        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
//        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){

            return $this->render('evaluation/cardio/fr_cardio-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/cardio/cardio-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }




    #[Route('workshop-content/{id}/evaluation/slow-sequence/answers', name: 'app_evaluation_slow_sequence_response')]
    public function responseQuizSlowSeq(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale(); // Langue courante (définie par le kernel)

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if ($locale=='fr'){
            return $this->render('evaluation/slow_sequence/fr_slow-seq-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/slow_sequence/slow-seq-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }




    #[Route('workshop-content/{id}/nasogastric/answers', name: 'app_evaluation_nasogastric_tub_response')]
    public function nasogastricQuizCardio(Request $request, int $id, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {
        $locale = $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $user = $this->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'id' => $id, 'expired' => "0", 'readed' => true]);

        if (!$workshopCart) {
            return $this->render('selfcare/404.html.twig');
        }

        if ($workshopCart->getPaymentMode() != '1') {
            // payment mode = 1 => credit card
            // payment mode = 2 => bank transfer
            // if is free and not expired show content else verify the proof file
            if (!$workshopCart->getIsFree()) {
                if ($workshopCart->getFile()->getStatus() != '1') {
                    return $this->render('selfcare/workshop_not_valid.html.twig', [
                        'workshop' => $workshopCart->getWorkshop(),
                    ]);

                }
            }
        }
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        if (  $locale=='fr'){
            return $this->render('evaluation/nasogastric/fr_nasogastric-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }else{
            return $this->render('evaluation/nasogastric/nasogastric-quiz-response.html.twig', [
                'workshop' => $workshopCart->getWorkshop(),
                'id_workshopCart'=>$workshopCart->getId(),
                'objectives' => $objectives,
                'totalRate5' => $totalRate5,
                'percentPerValueRate'=>$percentPerValueRate,
                'recomendedWorkshop'=>$recomendedWorkshop,
                'comments'=>$comments
            ]);
        }

    }





    public function calculateRate(ReviewRepository $reviewRepository, EntityManagerInterface $entityManager, int $id)
    {
        // count calculate percent rating
        $totalRating = $entityManager->getRepository(Review::class)
            ->createQueryBuilder('r')
            ->select('count(r.id)')
            ->Where('r.workshop = :idReview')
            ->andWhere('r.rate != :rateValue')
            ->setParameter('rateValue', 0)
            ->setParameter('idReview', $id)
            ->getQuery()
            ->getSingleScalarResult();
        $percentPerValueRate = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ];

        for ($i = 1; $i <= 5; $i++) {

            $total = $reviewRepository->findByRateValue($id, $i);
            if ($total > 0) {
                $percentPerValueRate["$i"] = round(($total / $totalRating) * 100, 2);
            }

        }
        return $percentPerValueRate;
    }


    public function recommmandedWorkshop($limit = null)
    {
        if ($limit == null) {
            $recommandedWorkShop = $this->doctrineManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1], ['consultedCount' => 'DESC'], 3);
        } else {
            $recommandedWorkShop = $this->doctrineManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1], ['consultedCount' => 'DESC'], $limit);
        }
//        $recommandedWorkShop = $this->doctrineManager->getRepository(Workshop::class)
//            ->findBy(['workshopStatus' => 1], ['consultedCount' => 'DESC'], 3);
        return $recommandedWorkShop;
    }

}
