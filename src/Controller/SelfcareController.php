<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\ProfilePic;
use App\Entity\Review;
use App\Entity\SelfcareUser;
use App\Entity\Theme;
use App\Entity\Speciality;
use App\Entity\Workshop;
use App\Entity\WorkshopCart;
use App\Entity\WorkShopObjectives;
use App\Entity\WorkShopSupport;

use App\Form\ProfilePicType;
use App\Form\UserType;
use App\Form\WorkshopType;
use App\Repository\ReviewRepository;
use ContainerDBjbYvt\getMimeTypesService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;
use function Symfony\Bundle\FrameworkBundle\Controller\redirect;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class SelfcareController extends AbstractController
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

    #[Route('/home', name: 'homepage')]
    public function index(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {

        return $this->redirectToRoute('theme_show_all');
        //return $this->render('sorry.html.twig');
        $session = $this->requestStack->getSession();
        $workshopType = 1;
        $workshopTypeRequest = $request->get('wt');
        if ($workshopTypeRequest) $workshopType = $workshopTypeRequest;

        $session->set('workshop-type', $workshopType);

        $entityManager = $doctrine->getManager();
        $workshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopStatus' => 1],
                ['workshopOrder' => 'ASC'], 5);
        $themes = $entityManager->getRepository(Theme::class)->findBy(['themeStatus' => 1],
            ['themeOrder' => 'ASC'], 5);
        $popularTheme = $themes[0];
        $popularWorkshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['theme' => $popularTheme->getId()],
                ['workshopOrder' => 'ASC']);
        $reviews = $entityManager->getRepository(Review::class)->findBy([], ['id' => 'DESC'], 6);

        return $this->render('selfcare/index.html.twig', [
            'workshops' => $workshops,
            'themes' => $themes,
            'popularWorkshops' => $popularWorkshops,
            'reviews' => $reviews
        ]);
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request, ManagerRegistry $doctrine): Response
    {
//        $session = $this->requestStack->getSession();
//        $workshopType = 1;
//        $workshopTypeSession = $session->get('workshop-type');
//        if ($workshopTypeSession) $workshopType = $workshopTypeSession;
//
//        $workshops = array();
//        $entityManager = $doctrine->getManager();
//
//        $submittedForm = $request->get('_submitS');
//        if ($request->getMethod() == 'POST' && isset($submittedForm)) {
//            $searchValue = $request->get('_search');
//            $workshops = $entityManager->getRepository(Workshop::class)
//                ->findBy(['name' => $searchValue, 'workshopType' => $workshopType, 'status' => 1]);
//        }
        $session = $this->requestStack->getSession();
        $workshopType = 1;
        $workshopTypeSession = $session->get('workshop-type');
        if ($workshopTypeSession) $workshopType = $workshopTypeSession;

        $workshops = array();
        $entityManager = $doctrine->getManager();

        $submittedForm = $request->get('_submitS');
        if ($request->getMethod() == 'POST') {
            $searchValue = $request->get('_search');
//            $workshops = $entityManager->getRepository(Workshop::class)
//                ->findBy(['name' => $searchValue, 'workshopType' => $workshopType, 'status' => 1]);
            $workshops = $entityManager->createQuery(
                "SELECT w
                 FROM  App\Entity\Workshop w
                 WHERE w.workshopStatus = 1 AND w.name LIKE  :name "
            )->setParameter('name', "%$searchValue%")->getResult();

        }

        return $this->render('selfcare/search.html.twig', [
            'workshops' => $workshops
        ]);
    }

//    #[Route('/theme-show-all', name: 'theme_show_all')]
//    #[Route('/', name: 'theme_show_all')]

//    #[Route('/{locale}', name: 'theme_show_all')]
    public function show(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator, string $locale = 'en'): Response
    {
//        $request->setLocale($locale);
        $session = $this->requestStack->getSession();
        $themes = $doctrine->getRepository(Theme::class)->findAll();


        $workshopTypeSession = $session->get('workshop-type');
        if ($workshopTypeSession) $workshopType = $workshopTypeSession;

        $entityManager = $doctrine->getManager();
        $theme = $doctrine->getRepository(Theme::class)->findOneBy(['id' => $request->get("them_id")]);
        if ($theme) {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1, 'theme' => $theme], ['consultedCount' => 'DESC']);
        } else {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1], ['consultedCount' => 'DESC']);
        }


        $workshopsThemes = array();
        foreach ($workshops as $key => $item) {
            $workshopsThemes[$item->getTheme()->getId()][0] = $item->getTheme();
            $workshopsThemes[$item->getTheme()->getId()][$key + 1] = $item;
        }
        return $this->render('selfcare/show_all_themes.html.twig', [
//            'themes' => $workshopsThemes,
            'themes' => $themes,
            'workshops' => $workshops,
        ]);
    }

    #[Route('/theme-show-all-students', name: 'theme_show_all_students')]
    public function showThemStudents(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $workshopTypeSession = $session->get('workshop-type');

        $entityManager = $doctrine->getManager();
        $theme = $doctrine->getRepository(Theme::class)->findOneBy(['id' => $request->get("them_id")]);
        if ($theme) {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1, 'theme' => $theme, 'workshopType' => 2], ['consultedCount' => 'DESC']);
        } else {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1, 'workshopType' => 2], ['consultedCount' => 'DESC']);
        }


        $themesId = [];
        foreach ($workshops as $key => $item) {
            $workshopsThemes[$item->getTheme()->getId()][$key + 1] = $item;
            array_push($themesId, $item->getTheme()->getId());
        }
        $themes = $doctrine->getRepository(Theme::class)->findBy(['id' => $themesId]);
        return $this->render('selfcare/show_all_themes_student.html.twig', [
            'themes' => $themes,
            'workshops' => $workshops,

        ]);
    }

    #[Route('/theme-show-all-professionals', name: 'theme_show_all_professionals')]
    public function showThemProfessionals(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $workshopTypeSession = $session->get('workshop-type');

        $entityManager = $doctrine->getManager();
        $theme = $doctrine->getRepository(Theme::class)->findOneBy(['id' => $request->get("them_id")]);
        if ($theme) {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1, 'theme' => $theme, 'workshopType' => 1], ['consultedCount' => 'DESC']);
        } else {
            $workshops = $entityManager->getRepository(Workshop::class)
                ->findBy(['workshopStatus' => 1, 'workshopType' => 1], ['consultedCount' => 'DESC']);
        }

        $themesId = [];
        foreach ($workshops as $key => $item) {

            $workshopsThemes[$item->getTheme()->getId()][$key + 1] = $item;
            array_push($themesId, $item->getTheme()->getId());
        }
        $themes = $doctrine->getRepository(Theme::class)->findBy(['id' => $themesId]);
        return $this->render('selfcare/show_all_themes_professionals.html.twig', [
            'themes' => $themes,
            'workshops' => $workshops,

        ]);
    }


    #[Route('/theme-show-{id}', name: 'workshop_by_theme')]
    public function workshopByTheme(ManagerRegistry $doctrine, int $id): Response
    {
        $session = $this->requestStack->getSession();
        $workshopType = 1;
        $workshopTypeSession = $session->get('workshop-type');
        if ($workshopTypeSession) $workshopType = $workshopTypeSession;

        $entityManager = $doctrine->getManager();
        $workshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopStatus' => 1, 'theme' => $id, 'workshopType' => $workshopType], ['workshopOrder' => 'ASC']);
        $theme = $entityManager->getRepository(Theme::class)->findOneBy(['id' => $id]);

        $consultedWorkshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopType' => $workshopType, 'workshopStatus' => 1],
                ['consultedCount' => 'DESC'], 4);
        $popularWorkshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopType' => $workshopType, 'workshopStatus' => 1],
                ['purchasedCount' => 'DESC'], 2);
        return $this->render('selfcare/workshop_show_by_theme.html.twig', [
            'workshops' => $workshops,
            'themeName' => $theme->getName(),
            'themeDescription' => $theme->getDescription(),
            'consultedWorkshops' => $consultedWorkshops,
            'popularWorkshops' => $popularWorkshops,
        ]);
    }


    #[Route('/workshop-show-{id}', name: 'workshop_show')]
    public function workshopById(Request $request, ManagerRegistry $doctrine, int $id, ReviewRepository $reviewRepository, EntityManagerInterface $entityManager): Response
    {
        $session = $this->requestStack->getSession();
        $workshopType = 1;
        $workshopTypeSession = $session->get('workshop-type');
        if ($workshopTypeSession) $workshopType = $workshopTypeSession;

        $entityManager = $doctrine->getManager();
        $workshop = $entityManager->getRepository(Workshop::class)
            ->findOneBy(['id' => $id, 'workshopStatus' => 1]);
        //->findOneBy(['workshopType' => $workshopType, 'id' => $id, 'workshopStatus' => 1]);


        $consultedWorkshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopStatus' => 1],
                ['consultedCount' => 'DESC'], 4);

        $popularWorkshops = $entityManager->getRepository(Workshop::class)
            ->findBy(['workshopType' => $workshopType, 'workshopStatus' => 1],
                ['purchasedCount' => 'DESC'], 2);
        $consultedCount = $workshop->getConsultedCount();
        if (isset($consultedCount)) {
            $consultedCount++;
            $workshop->setConsultedCount($consultedCount);
            $entityManager->flush();
        }
        $addToCart = null;
        $existe = null;
        if ($request->get('addToCart')) $addToCart = true;
        if ($request->get('existe')) $existe = true;
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)
            ->findBy(['workshop' => $workshop->getId(), 'objectiveStatus' => 1]);
        $images = $entityManager->getRepository(WorkShopSupport::class)
            ->findBy(['workshop' => $workshop->getId(), 'supportStatus' => 1, 'supportType' => 'Image']);
        $reviews = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $id], ['id' => 'DESC'], 6);
        // count rating how rate 5 star
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);


        return $this->render('selfcare/workshop_show.html.twig', [
            'workshop' => $workshop,
            'consultedWorkshops' => $consultedWorkshops,
            'popularWorkshops' => $popularWorkshops,
            'objectives' => $objectives,
            'images' => $images,
            'addToCart' => $addToCart,
            'existe' => $existe,
            'reviews' => $reviews,
            'totalRate5' => $totalRate5,
            'percentPerValueRate' => $percentPerValueRate
        ]);
    }


    #[Route('/add-to-cart-{id}', name: 'workshop_reserve')]
    public function workshopReserve(ManagerRegistry $doctrine, int $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'workshop' => $id, 'status' => ['1', '0'], 'expired' => 0]);// add to card
        $existe = false;
        if ($workshopCart) {
            $workshopCart->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            $existe = true;
        } else {
            $workshop = $entityManager->getRepository(Workshop::class)
                ->findOneBy(['id' => $id, 'workshopStatus' => 1]);
            $workshopCart = new WorkshopCart();
            $workshopCart->setWorkshop($workshop);
            $workshopCart->setSelfcareUer($user);
            $workshopCart->setStatus('0');
            $workshopCart->setPaymentAmount($workshop->getPrice());
            $workshopCart->setExpired(0);
            $workshopCart->setUpdatedAt(new \DateTime());
            $entityManager->persist($workshopCart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('workshop_show', ['id' => $id, 'addToCart' => true, 'existe' => $existe]);
    }


    #[Route('/buy-now-{id}', name: 'workshop_buy_now')]
    public function workshopBuyNow(ManagerRegistry $doctrine, int $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'workshop' => $id, 'status' => 0]);

        if ($workshopCart) {
            $workshopCart->setUpdatedAt(new \DateTime());
            $entityManager->flush();
        } else {
            $workshop = $entityManager->getRepository(Workshop::class)
                ->findOneBy(['id' => $id, 'workshopStatus' => 1]);
            $workshopCart = new WorkshopCart();
            $workshopCart->setWorkshop($workshop);
            $workshopCart->setSelfcareUer($user);
            $workshopCart->setStatus('0');
            $workshopCart->setExpired(0);
            $workshopCart->setUpdatedAt(new \DateTime());
            $entityManager->persist($workshopCart);
            $entityManager->flush();
        }
        return $this->redirectToRoute('workshop_cart');
    }

    #[Route('/delete-from-cart-{id}', name: 'workshop_delete_from_cart')]
    public function workshopDeleteFromCart(ManagerRegistry $doctrine, int $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'workshop' => $id, 'status' => 0]);

        if ($workshopCart) {
            $entityManager->remove($workshopCart);
            $entityManager->flush();
        }
        return $this->redirectToRoute('workshop_cart');
    }

    #[Route('/cart', name: 'workshop_cart')]
    public function workshopCart(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => '0']);
        $total = 0;
        $dateNow = date('Y-m-d H:i:s');
        foreach ($workshopCarts as $workshopCart) {
            $createdAt = $workshopCart->getCreatedAt()->format('Y-m-d H:i:s');
            $createdAt = date('Y-m-d H:i:s', strtotime($createdAt . '+ 1 days'));
            if ($dateNow > $createdAt) {
                $entityManager->remove($workshopCart);
                $index = array_search($workshopCart, $workshopCarts);
                unset($workshopCarts[$index]);
            } else {
                if (!($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                    is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles())))) {
                    $total += number_format(floatval(str_replace(',', '.', $workshopCart->getWorkshop()->getPrice())), 3, '.', '');
                }
//                $total += number_format(floatval(str_replace(',', '.', $workshopCart->getWorkshop()->getPrice())), 3, '.', '');
            }
        }
        $entityManager->flush();

        $recommandedWorkShop = $this->recommmandedWorkshop();


        if ($request->get('confirmCart')) {
            return $this->render('selfcare/confirm_cart.html.twig', [
                'workshopCarts' => $workshopCarts,
                'total' => $total,
                'recommandedWorkShop' => $recommandedWorkShop
            ]);
        } else {
            return $this->render('selfcare/cart.html.twig', [
                'workshopCarts' => $workshopCarts,
                'total' => $total,
                'recommandedWorkShop' => $recommandedWorkShop
            ]);
        }
    }


    #[Route('/workshop/show-content/{id}', name: 'workshop_show_content')]
    public function workshopContentById(Request          $request, ManagerRegistry $doctrine, int $id,
                                        ReviewRepository $reviewRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'workshop' => $id, 'expired' => "0"]);

        if (!$workshopCart) {
            $this->addFlash('error',"The workshop has expired. Please purchase it again.");
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

        // check workshopcart is valid
        $this->checkCartIsValid($workshopCart);
        $entityManager->flush();
        if ($workshopCart->getExpired()) {
            return $this->render('selfcare/404.html.twig');
        }
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'objectiveStatus' => 1]);
        $images = $entityManager->getRepository(WorkShopSupport::class)->findBy(['workshop' => $workshopCart->getWorkshop()->getId(), 'supportStatus' => 1, 'supportType' => 'Image']);
        $courseContent = $entityManager->getRepository(WorkShopSupport::class)
            ->findBy([
                'workshop' => $workshopCart->getWorkshop()->getId(),

                'supportStatus' => 1
            ]);
//        'supportType' => 'Video',
        $videoSuuport = $entityManager->getRepository(WorkShopSupport::class)
            ->findOneBy(['workshop' => $workshopCart->getWorkshop()->getId(),
                'supportType' => 'Video', 'supportStatus' => 1]);

        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => 1, 'expired' => 0]);
        $comments = $entityManager->getRepository(Review::class)
            ->findBy(['workshop' => $workshopCart->getWorkshop()->getId()], ['id' => 'DESC'], 5);

        // calculateRating
        $totalRate5 = $reviewRepository->findByRateValue($id, 5);
        $percentPerValueRate = $this->calculateRate($reviewRepository, $entityManager, $id);
        $recomendedWorkshop = $this->recommmandedWorkshop(6);


        return $this->render('selfcare/workshop_show_content.html.twig', [
            'workshop' => $workshopCart->getWorkshop(),
            'workshopCart_id' => $workshopCart->getId(),
            'workshopCard' => $workshopCart,
            'objectives' => $objectives,
            'courseContent' => $courseContent,
            'workshopCarts' => $workshopCarts,
            'videoSupport' => $videoSuuport,
            'comments' => $comments,
            'percentPerValueRate' => $percentPerValueRate,
            'totalRate5' => $totalRate5,
            'recomendedWorkshop' => $recomendedWorkshop
        ]);
    }

    #[Route('/my-profile', name: 'my_profile')]
    public function myProfile(Request         $request, UserPasswordHasherInterface $userPasswordHasher,
                              ManagerRegistry $doctrine): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $country = $entityManager->getRepository(Country::class)->findAll();

        $submittedForm = $request->get('_submit');
        if ($request->getMethod() == 'POST' && isset($submittedForm)) {
            $userName = $request->get('name') . ' ' . $request->get('lastname');
            if ($request->get('name')) $user->setName($request->get('name'));
            if ($request->get('lastname')) $user->setLastName($request->get('lastname'));
            if ($userName) $user->setFullName($userName);
            $user->setPhoneNumber($request->get('_phone'));
            $user->setUserAddress($request->get('_address'));
            $cnt = $entityManager->getRepository(Country::class)->findOneBy(['id' => $request->get('_country')]);
            if ($cnt) $user->setCountry($cnt->getNationality());
            $user->setEstablishment($request->get('_institution'));
            $user->setSpeciality($request->get('_speciality'));
            $user->setUserAddress($request->get('address'));

            $user->setDescription($request->get('_description'));
            $user->setLevel($request->get('etude'));
            $entityManager->flush();
        }

        $submittedFormPWD = $request->get('_submit_pwd');
        if ($request->getMethod() == 'POST' && isset($submittedFormPWD)) {

            // encode the plain password
            $oldPwd = $userPasswordHasher->hashPassword(
                $user,
                $request->get('_old')
            );


            if (!password_verify($request->get('_old'), $user->getPassword())) {
                $this->addFlash('error', 'Please check your old password!');
            } else {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->get('_new')
                    )
                );
                $entityManager->flush();
            }
        }

        //$submitedPhoto = $request->get('_submit_photo');
        $profilePic = $doctrine->getRepository(ProfilePic::class)->findOneBy(['user' => $user]);
        if (!$profilePic) {
            $profilePic = new ProfilePic();
        }


        /*if ($form->isSubmitted() && $form->isValid()  && isset($submitedPhoto)) {
            $user = $form->getData();

            $entityManager->flush();
//            dd($user);die();
        }*/

//        if ($request->getMethod() == 'POST' && isset($submitedPhoto)) {
//            $data = $request->files->get('profilePictureUpdate');
//
//            $user->setImageFile( $data);
//
//            $entityManager->flush();
//        }


        return $this->render('selfcare/profile.html.twig', [
            'user' => $user,
            'profilePic' => $profilePic,
            'country' => $country
            //'form'=>$form->createView()
        ]);
    }

    #[Route('/my-learning', name: 'my_learning')]
    public function myLearning(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $profilePic = $doctrine->getRepository(ProfilePic::class)->findOneBy(['user' => $user]);
        $workshopCartsOnPay = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => '1', 'paymentMode' => '1']);
        $workshopCartsFile = $entityManager->getRepository(WorkshopCart::class)->findByValidateFile($user);
        $workshopCarts = array_merge($workshopCartsOnPay, $workshopCartsFile);
        usort($workshopCarts, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        // recommanded workshop order by consulted workshop

        $recommandedWorkShop = $this->recommmandedWorkshop();


        foreach ($workshopCarts as $workshopCart) {
            if ($workshopCart->getPayedAt()) $this->checkCartIsValid($workshopCart);
        }

        $entityManager->flush();
        return $this->render('selfcare/profile_learnings.html.twig', [
            'workshopCarts' => $workshopCarts,
            'user' => $user,
            'paymentAuthorization' => $request->get('paymentAuthorization'),
            'paymentReference' => $request->get('paymentReference'),
            'profilePic' => $profilePic,
            'recommandedWorkShop' => $recommandedWorkShop
        ]);
    }

    #[Route('/my-learning-history', name: 'my_learning_history')]
    public function myLearningHistory(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();

        $profilePic = $doctrine->getRepository(ProfilePic::class)->findOneBy(['user' => $user]);
        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => ['1', '2']]);

        return $this->render('selfcare/purchase_history.html.twig', [
            'workshopCarts' => $workshopCarts,
            'user' => $user,
            'profilePic' => $profilePic
        ]);
    }


    /*
     * set expiration date value into WorkshopCart
     */
    public function setExpirationDate(WorkshopCart $workshopCart)
    {
        //dump('la');die;
        if ($workshopCart->getStatus() != '0') {
            $expiredDate = $workshopCart->getWorkshop()->getExpirationDate();
            if (!$expiredDate['expirationDate']) {
                $unitNumber = intval($expiredDate['unitNumber']);
                $payedAt = $workshopCart->getPayedAt();
                $expiredDate = date('Y-m-d H:i:s', strtotime($payedAt . '+' . $unitNumber . ' ' . $expiredDate['unit']));
            }
            $expiredDate = new \DateTime($expiredDate);
            $workshopCart->setExpirationDate($expiredDate);
        }

    }

    /*
     *  check workshop cart is valid
     */
    public function checkCartIsValid(WorkshopCart $workshopCart)
    {
        $dateNow = (new \DateTime())->format('Y-m-d H:i:s');
        $expirationDate = $workshopCart->getExpirationDate();

        if ($expirationDate) {
            $expirationDate = $expirationDate->format('Y-m-d H:i:s');
            if ($expirationDate < $dateNow) {
                $workshopCart->setExpired(1);
                $workshopCart->setStatus('2');
            }
        }
    }


    #[Route('/profile-pic/update', name: 'update_profile_pic')]
    public function createProfilePic(Request $request, ManagerRegistry $doctrine, ProfilePic $profilePic = null): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();

        $profilePic = $doctrine->getRepository(ProfilePic::class)->findOneBy(['user' => $user]);

        $newPic = false;
        if (!$profilePic) {
            $newPic = true;
            $profilePic = new ProfilePic();
            $profilePic->setUser($user);
        }
        $form = $this->createForm(ProfilePicType::class, $profilePic);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $profilePic = $form->getData();
            $entityManager = $doctrine->getManager();
            if ($newPic) {
                $entityManager->persist($profilePic);
            }
            $entityManager->flush();
            return $this->redirectToRoute('my_profile');
        }

        return $this->renderForm('profile/update.html.twig', [
            'form' => $form,
            'user' => $user,
            'profilePic' => $profilePic
        ]);
    }

    #[Route('/contact', name: 'selfcare_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        if ($request->getMethod() == 'POST') {

            $adminEmails = explode(",", $_ENV['EMAIL_ADMIN']);

            $email = (new TemplatedEmail())
                ->from('courses.honorismedicalsimulation@universitecentrale.tn')
                ->to(...$adminEmails)
                ->cc($request->get('email'))
                ->subject($request->get('subject'))
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'name' => $request->get('name'),
                    'message' => $request->get('message'),
                ]);
            $mailer->send($email);
            $this->addFlash('success', 'Message');
        }
        return $this->renderForm('selfcare/contact.html.twig', [

        ]);
    }

    #[Route('/contact-component', name: 'selfcare_contact_component')]
    public function contactComponent(Request $request, MailerInterface $mailer): Response
    {
        if ($request->getMethod() == 'POST') {

            $adminEmails = explode(",", $_ENV['EMAIL_ADMIN']);
            $email = (new TemplatedEmail())
                ->from('courses.honorismedicalsimulation@universitecentrale.tn')
                ->to(...$adminEmails)
                ->cc($request->get('email'))
                ->subject($request->get('subject'))
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'name' => $request->get('name'),
                    'message' => $request->get('message'),
                ]);
            $mailer->send($email);
            return $this->redirectToRoute('homepage');

        }
        return $this->renderForm('selfcare/contact-component.html.twig', [

        ]);
    }


    #[Route('/workshop-review/add-{id}', name: 'selfcare_workshop_review_add')]
    public function addReview(Request $request, EntityManagerInterface $entityManager, int $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();

        if ($request->getMethod() == 'POST') {

            $workshop = $this->doctrineManager->getManager()->getRepository(Workshop::class)->findOneBy(['id' => $id]);
            if (!$workshop) {
                $this->addFlash('error', 'workshop does not exist! ');
                return $this->redirectToRoute('workshop_show_content', ['id' => $id]);
            } else {


                $review = new Review();
                $review->setUser($user);
                $review->setWorkshop($workshop);

                $review->setComment($request->get('comment'));
                $rating = $request->get('rating3');
                if (!$rating) {
                    $review->setRate('0');
                } else {
                    $review->setRate($rating);
                }
                $totale = $entityManager->getRepository(Review::class)
                    ->createQueryBuilder('r')
                    ->select('count(r.id)')
                    ->Where('r.workshop = :idWorkshop')
                    ->setParameter('idWorkshop', $workshop->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

                $sumRate = $entityManager->getRepository(Review::class)
                    ->createQueryBuilder('r')
                    ->select('sum(r.rate)')
                    ->Where('r.workshop = :idWorkshop')
                    ->setParameter('idWorkshop', $id)
                    ->getQuery()
                    ->getSingleScalarResult();
                $rate = 0;
                if ($totale > 0) {

                    $rate = round($sumRate / $totale, 2);

                }
                $workshop->setRate($rate);

                $this->doctrineManager->getManager()->persist($review);
                $this->doctrineManager->getManager()->flush();

                $this->addFlash('sucess', 'comment added successfully! ');
                return $this->redirectToRoute('workshop_show_content', ['id' => $id]);
            }
        } else {
            return $this->redirectToRoute('homepage');
        }

    }

    #[Route('/cart/modal-cart', name: 'modal_cart')]
    public function modalCart(EntityManagerInterface $entityManager)
    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->render('selfcare/modal-cart.html.twig', [
                'workshopCarts' => [],
                'total' => 0,
            ]);

        $user = $this->security->getUser();
        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => '0']);
        $total = 0;
        $dateNow = date('Y-m-d H:i:s');
        foreach ($workshopCarts as $workshopCart) {
            $createdAt = $workshopCart->getCreatedAt()->format('Y-m-d H:i:s');
            $createdAt = date('Y-m-d H:i:s', strtotime($createdAt . '+ 1 days'));
            if ($dateNow > $createdAt) {
                $entityManager->remove($workshopCart);
                $index = array_search($workshopCart, $workshopCarts);
                unset($workshopCarts[$index]);
            } else {
                if (!($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                    is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles())))) {
                    $total += number_format(floatval(str_replace(',', '.', $workshopCart->getWorkshop()->getPrice())), 3, '.', '');
                }

            }
        }
        $entityManager->flush();

        return $this->render('selfcare/modal-cart.html.twig', [
            'workshopCarts' => $workshopCarts,
            'total' => $total,
        ]);
    }

    #[Route('/delete-from-modal-cart-{id}', name: 'workshop_delete_from_mod_cart')]
    public function workshopDeleteFromModalCart(ManagerRegistry $doctrine, int $id): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findOneBy(['selfcareUer' => $user, 'workshop' => $id, 'status' => 0]);

        if ($workshopCart) {
            $entityManager->remove($workshopCart);
            $entityManager->flush();
        }
        return $this->redirectToRoute('modal_cart');
    }


    /**
     * @Route("/total-workshop", methods="GET", name="app_total_workshop")
     *
     */
    public function totalWorkshop(Request $request, EntityManagerInterface $entityManager): Response
    {
        $result = ['total' => 0];
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL'))) {
            return $this->json($result);
        }


        $user = $this->security->getUser();

        $workshopCart = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['status' => 0, 'selfcareUer' => $user->getId()]);

        $result = ['total' => count($workshopCart)];

        return $this->json($result);
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

    #[Route('/api/update-workshopcart', name: 'app_api_update_wrk_shop_crt_add', methods: ['POST', 'GET'])]
    public function getAvailable(Request $request, EntityManagerInterface $em): Response
    {

        $data = $request->get('id');
        $type = $request->get('type');


        if (isset($data)) {
            // type = 1 update readed
            // type = 2 update quiz passed
            if ($type == 1) {
                $workshopCard = $em->getRepository(WorkshopCart::class)->findOneBy(['id' => $data]);
                if ($workshopCard) {
                    $workshopCard->setReaded(true);

                }
            }
            if ($type == 2) {
                $workshopCard = $em->getRepository(WorkshopCart::class)->findOneBy(['id' => $data]);
                if ($workshopCard) {
                    $workshopCard->setQuizPassed(true);
                }
            }

            $em->flush();
            return $this->json([
                'msg' => 'sucess'
            ]);
        } else {
            return $this->json(['message' => 'données incomplètes'], 404);
        }


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


    #[Route('/equipment', name: 'honoris_equipment')]
    public function equipment(): Response
    {
        return $this->render('equipment/equipment.html.twig');

    }
}