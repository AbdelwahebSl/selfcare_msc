<?php

namespace App\Controller;

use App\Entity\CartFile;
use App\Entity\SelfcareUser;
use App\Entity\Theme;
use App\Entity\Speciality;
use App\Entity\Workshop;
use App\Entity\WorkshopCart;
use App\Entity\WorkShopObjectives;
use App\Entity\WorkShopSupport;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Bundle\FrameworkBundle\Controller\redirect;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;


class AdminController extends AbstractController
{

    private $requestStack;
    private $security;

    public function __construct(RequestStack $requestStack, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
    }


    #[Route('/admin-workshops', name: 'workshops_list'), ]
    public function index(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $workshops = $entityManager->getRepository(Workshop::class)
            ->findBy(array(), array('workshopOrder' => 'ASC'));


        return $this->render('admin/workshops.html.twig', [
            'workshops' => $workshops,
            'user' => $user,
        ]);
    }


    #[Route('/admin-themes', name: 'themes_list')]
    public function getThemes(ManagerRegistry $doctrine): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $themes = $entityManager->getRepository(Theme::class)
            ->findAll();
        return $this->render('admin/themes.html.twig', [
            'themes' => $themes,
            'user' => $user,
        ]);
    }

    #[Route('/admin-specialities', name: 'specialities_list')]
    public function getSpecialities(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');


        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $specialities = $entityManager->getRepository(Speciality::class)
            ->findAll();
        return $this->render('admin/specialities.html.twig', [
            'specialities' => $specialities,
            'user' => $user,
        ]);
    }

    #[Route('/admin-objectives', name: 'objectives_list')]
    public function getObjectives(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');


        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $objectives = $entityManager->getRepository(WorkShopObjectives::class)
            ->findAll();
        return $this->render('admin/objectives.html.twig', [
            'objectives' => $objectives,
            'user' => $user,
        ]);
    }

    #[Route('/admin-supports', name: 'supports_list')]
    public function getSupports(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');


        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $supports = $entityManager->getRepository(WorkShopSupport::class)
            ->findAll();
        return $this->render('admin/supports.html.twig', [
            'supports' => $supports,
            'user' => $user,
        ]);
    }


    #[Route('/admin-cart', name: 'cart_list')]
    public function getCarts(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_MKG') || $this->isGranted('ROLE_ADMIN')))
            return $this->redirectToRoute('homepage');

        $user = $this->security->getUser();
        $entityManager = $doctrine->getManager();
        $cartfFiles = $entityManager->getRepository(CartFile::class)
            ->findAll(['status' => '1']);
        $workshpCarts = $entityManager->getRepository(WorkshopCart::class)->findByFile();

        return $this->render('admin/workshop_carts.html.twig', [
            'cartFiles' => $cartfFiles,
            'user' => $user,
            'workshpCarts' => $workshpCarts
        ]);
    }


    #[Route('/admin-cart/bank-tranfer/complete-payment/{id}', name: 'bank_transfer_complete_payment')]
    public function workshopBankTransferCompletePayment(Request $request, ManagerRegistry $doctrine, int $id, MailerInterface $mailer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isGranted('ROLE_ADMIN'))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $cartFile = $entityManager->getRepository(CartFile::class)
            ->findOneBy(['id' => $id]);

        if (!$cartFile) {
            return $this->render('selfcare/404.html.twig', ['reference' => 'File does not exist !']);
        }

        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['file' => $cartFile]);
        $total = 0;
        if ($workshopCarts) $total = $this->sumWorkshopCarts($workshopCarts);
        if ($request->getMethod() == 'POST') {
            $action = $request->get('action');
            $comment = $request->get('comment');
            $dateNow = new \DateTime();
            $dateNowF = $dateNow->format('Y/m/d H:i:s');
            $email = (new Email())
                ->from('courses.honorismedicalsimulation@universitecentrale.tn')
                ->to($workshopCarts[0]->getSelfcareUer()->getEmail());
            if ($action == 'accept') {
                foreach ($workshopCarts as $workshopCart) {
                    $workshopCart->setPayedAt($dateNowF);
                    $purchasedCount = $workshopCart->getWorkshop()->getPurchasedCount() + 1;
                    $workshopCart->getWorkshop()->setPurchasedCount($purchasedCount);
                    $workshopCart->setUpdatedAt($dateNow);
                    $workshopCart->setStatus('1');
                    $this->setExpirationDate($workshopCart);
                }
                $cartFile->setPayedAt(date('Y-m-d H:i:s'));
                $cartFile->setStatus('1');
                $email->subject('Approval file accepted!')
                    ->html('<p>Your approval file has been accepted  </p>' . $comment);


            } else if ($action == 'refuse') {
                foreach ($workshopCarts as $workshopCart) {
                    $workshopCart->setUpdatedAt($dateNow);
                }
                $cartFile->setStatus('2');
                $email->subject('Approval file refused!')
                    ->html('<p>Your approval file has been refused </p>' . $comment);


            }

            $cartFile->setApprovalUser($user->getFullName());
            $cartFile->setDescription($comment);
            $entityManager->flush();
            $mailer->send($email);
            return $this->redirectToRoute('cart_list');

        }

        return $this->render('admin/edit_file_cart.html.twig', [
            'total' => $total,
            'workshopCarts' => $workshopCarts,
            'cartFile' => $cartFile,
            'user' => $user
        ]);
    }


    /*
  *  calculate the sum  of workshopcart
  */
    public function sumWorkshopCarts(array $workshopCarts)
    {
        $total = 0;
        foreach ($workshopCarts as $workshopCart) {
            $workshopName = $workshopCart->getWorkshop()->getName();
            $workshopPrice = intval($workshopCart->getWorkshop()->getPrice());
            $total += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
        }
        return $total;
    }

    /*
    * set expiration date value into WorkshopCart
    */
    public function setExpirationDate(WorkshopCart $workshopCart)
    {
        if ($workshopCart->getStatus() != '0') {
            $expiredDate = $workshopCart->getWorkshop()->getExpirationDate();
            if (!$expiredDate['expirationDate']) {
                $unitNumber = intval($expiredDate['unitNumber']);
                $payedAt = $workshopCart->getPayedAt();
                $expiredDate = date('Y-m-d H:i:s', strtotime($payedAt . '+' . $unitNumber . ' ' . $expiredDate['unit']));
            } else {
                $expiredDate = $expiredDate['expirationDate'];
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

        $expirationDate = $expirationDate->format('Y-m-d H:i:s');
        if ($expirationDate) {
            if ($expirationDate < $dateNow) {
                $workshopCart->setExpired(1);
                $workshopCart->setStatus('2');
            }
        }
    }
}