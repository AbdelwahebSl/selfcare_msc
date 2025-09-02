<?php

namespace App\Controller;

use App\Entity\CartFile;
use App\Entity\WorkshopCart;
use App\Form\PaymentFileType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class PaymentController extends AbstractController
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


    #[Route('/workshop/cart/payment', name: 'cart_payment')]
    public function workshopCartPayment(Request $request, ManagerRegistry $doctrine, LoggerInterface $logger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');


        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();
        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => 0]);
        $total = 0;
        $transactionId = '';
        $workshopId = '';
        $workshopInitials = '';
        foreach ($workshopCarts as $workshopCart) {
            $transactionId .= $workshopCart->getId();
            $workshopId .= $workshopCart->getWorkshop()->getId();
            $workshopName = $workshopCart->getWorkshop()->getName();
            $workshopInitials .= substr($workshopName, 0, 1);
            $workshopPrice = intval($workshopCart->getWorkshop()->getPrice());
            $workshopCart->setIsFree(false);
            if ($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles()))) {
                // workshop  is free for student Honoris
                $workshopCart->setIsFree(true);
            }

            if (!($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles())))) {
                $total += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
            }

//            $total += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
        }
        $workshopInitials .= '1234567890002202';
        $transactionId = "$transactionId" . str_shuffle($workshopInitials) . "$workshopId";
        foreach ($workshopCarts as $workshopCart) {
            $workshopCart->setPaymentTransactionId($transactionId);
        }
        $entityManager->flush();

        $dnsS = $this->getParameter('app.msc_dns');
        $confirmPaymentUrl = $this->generateUrl('cart_payment_complete', array(), true);
        $confirmPaymentUrl = "$dnsS$confirmPaymentUrl";
        $paymentParams = array(
            'password' => $_ENV['SMT_PASSWORD'],
            'userName' => $_ENV['SMT_USERNAME'],
//            'password' => $_ENV['SMT_PASSWORD'],
//             'userName' => $_ENV['SMT_USERNAME'],
            'orderNumber' => $transactionId,
            'amount' => $total * 1000,
            'currency' => '788',
            'language' => 'FR',
            'returnUrl' => $confirmPaymentUrl
        );
//        dump($_ENV['SMT_PASSWORD']);dump($_ENV['SMT_USERNAME']); dump($paymentParams);die();
        $paymentHttpQuery = urldecode(http_build_query($paymentParams));
        $clicktopayA = $this->getParameter('app.clicktopay_reg');
        $smtUri = "$clicktopayA?$paymentHttpQuery";

        $client = HttpClient::create(['verify_peer' => false, 'verify_host' => false]);
//        try {
        $response = $client->request('GET', $smtUri);
//            dd($response);die();
        $logger->alert('***onlinePaymentReq**' . json_encode($smtUri) .
            '***onlinePaymentRes**' . json_encode($response->toArray()));

        $statusCode = $response->getStatusCode();
//            dd($response->toArray());
        if ($statusCode == 200) {
            $content = $response->toArray();
            if (isset($content['orderId']) && isset($content['formUrl'])) {
                foreach ($workshopCarts as $workshopCart) {
                    $workshopCart->setOrderIdSMT($content['orderId']);
                }
                $entityManager->flush();
                return $this->redirect($content['formUrl']);
            }
        } else {
            return $this->render(
                'selfcare/404.html.twig',
                array('reference' => '  An error has occurred, please try again later!')
            );
        }
//        } catch (\Throwable $exception) {
////////            $this->logger->alert('***error_create_meet**' . json_encode($jsonEvent) . '***error_create_meet**');
//////
////////            if ($exception) {
////////                $content = json_decode($exception->getResponse()->getContent(false), true);
////////                dump($content);
//                dump($exception->getMessage());
////////            }
//            dd($exception);die();
//        }

        return $this->render('selfcare/404.html.twig');
    }


    #[Route('/workshop/cart/payment-complete', name: 'cart_payment_complete')]
    public function workshopCartPaymentComplete(Request $request, ManagerRegistry $doctrine, LoggerInterface $logger): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $orderId = $request->get('orderId');
        $entityManager = $doctrine->getManager();

        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['orderIdSMT' => $orderId, 'status' => 0]);
        if (empty($workshopCarts)) {
            return $this->render(
                'selfcare/404.html.twig',
                array('reference' => $orderId)
            );

        }

        $paymentReference = $workshopCarts[0]->getPaymentTransactionId();
        $transactionAmount = 0;
        foreach ($workshopCarts as $workshopCart) {
            $workshopPrice = intval($workshopCart->getWorkshop()->getPrice());
            if (!($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles())))) {
                $transactionAmount += number_format(floatval(str_replace(',', '.', $workshopCart->getWorkshop()->getPrice())), 3, '.', '');
            }
//            $transactionAmount += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
        }

        $paymentParams = array(
            'password' => $_ENV['SMT_PASSWORD'],
            'userName' => $_ENV['SMT_USERNAME'],
//            'password' => $_ENV['SMT_PASSWORD'],
//             'userName' => $_ENV['SMT_USERNAME'],
            'orderId' => $orderId,
            'language' => 'en',
        );
        $paymentHttpQuery = urldecode(http_build_query($paymentParams));
//        dump($paymentHttpQuery);
//        dump($paymentHttpQuery);
        $clicktopayO = $this->getParameter('app.clicktopay_os');
        $smtUri = "$clicktopayO?$paymentHttpQuery";
//        dump($smtUri);

        $client = HttpClient::create(['verify_peer' => false, 'verify_host' => false]);
        $response = $client->request('GET', $smtUri);


        $logger->alert('***onlinePaymentStatusReq**' . json_encode($smtUri) . '***onlinePaymentStatusRes**' . json_encode($response->toArray()));
//        dump($response->toArray());
//        dd($response);die();
//        $credentialsSMT[0] = '100090380';
//        $credentialsSMT[1] = 'fm87UpS6J';
        if ($response->getStatusCode() == 200) {

            $content = $response->toArray();

            if (isset($content['cardAuthInfo']['approvalCode'])) {
                if ($content['cardAuthInfo']['approvalCode'] != null && $content['cardAuthInfo']['approvalCode'] != 0) {
                    $logger->alert('***onlinePaymentStatusReq**' . json_encode($smtUri) . '***onlinePaymentStatusRes**' . json_encode($response->toArray()));
//orderStatus=2 pour le paiement validé
                    $dateNow = new \DateTime();
                    $dateNowF = $dateNow->format('Y/m/d H:i:s');

                    foreach ($workshopCarts as $workshopCart) {
//                        dd("payment with credit cart status 1");

                        $workshopCart->setStatus('1');//Payed
                        $workshopCart->setPaymentMode('1');// payment with credit cart

                        //set expiration date
                        $workshopCart->setExpired(0);
                        $workshopCart->setPayedAt($dateNowF);
                        $this->setExpirationDate($workshopCart);
//dd($response->toArray());die();

                        //Set Click to pay details
                        $workshopCart->setSmtPayedAt($dateNowF);
                        $workshopCart->setPaymentAuthorization($content['cardAuthInfo']['approvalCode']);

                        //Count purchase
                        $purchasedCount = $workshopCart->getWorkshop()->getPurchasedCount();
                        $purchasedCount++;
                        $workshopCart->getWorkshop()->setPurchasedCount($purchasedCount);
                    }
                    $entityManager->flush();

                    return $this->redirectToRoute('my_learning', [
                        'paymentAuthorization' => $content['cardAuthInfo']['approvalCode'],
                        'paymentReference' => $paymentReference,
                    ]);
                }
            } else {
                // payment error.
                $logger->alert('***onlinePaymentStatusReq**' . json_encode($smtUri) . '***onlinePaymentStatusRes**' . json_encode($response->toArray()));

                $errorMessage = 'An error has occurred, please try again later';

//                if ($content['errorCode'] == '0') {
//                    $errorMessage = 'Transaction was refused.';
//                }
//                elseif ($content['errorCode'] == '1') {
//                    $errorMessage = 'The order number is duplicated, the order with the specified order number is already processed';
//                }
//                elseif ($content['errorCode'] == '3') {
//                    $errorMessage = 'Currency unknown.';
//
//                }
//                elseif ($content['errorCode'] == '4') {
//                    $errorMessage = 'The required query parameter was not specified.';
//
//                }
//                elseif ($content['errorCode'] == '5') {
//                    $errorMessage = 'Wrong value of a request parameter.';
//                }
//                elseif ($content['errorCode'] == '7') {
//                    $errorMessage = 'System error.';
//                }


                return $this->render(
                    'selfcare/404.html.twig',
                    array('reference' => $paymentReference . ' ' . $errorMessage)
                );
            }
        } else {
            $logger->alert('***onlinePaymentStatusReq**' . json_encode($smtUri) . '***onlinePaymentStatusRes**' . json_encode($response->toArray()));
            return $this->render(
                'selfcare/404.html.twig',
                array('reference' => $paymentReference . '  An error has occurred, please try again later!')
            );

        }
        return $this->render(
            'selfcare/404.html.twig',
            array('reference' => $paymentReference)
        );
    }

    #[Route('/workshop/for-free/payment', name: 'bank_for_free_payment')]
    public function forFreePayment(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT')))
            return $this->redirectToRoute('homepage');
        $user = $this->security->getUser();
        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => '0']);
        $myLearning = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'expired' => 0, 'status' => ['1']]);
        $total = 0;

        if (!$workshopCarts) {
            return $this->render('selfcare/404.html.twig', ['reference' => '']);
        }

        foreach ($workshopCarts as $workshopCart) {
            foreach ($myLearning as $item) {
                if ($item->getWorkshop() == $workshopCart->getWorkshop() and
                    !$item->getExpired()) {
                    $entityManager->remove($workshopCart);
                }
            }
        }

        foreach ($workshopCarts as $workshopCart) {
            $workshopCart->setStatus('1');
            $workshopCart->setPaymentMode('1');
            $workshopCart->setIsFree(true);
            $this->setExpirationDate($workshopCart);
//            $total += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
        }
        $entityManager->flush();

        return $this->redirectToRoute('my_learning');


    }

    #[Route('/workshop/bank-transfer/payment', name: 'bank_transfer_payment')]
    public function workshopBankTransferPayment(Request          $request, ManagerRegistry $doctrine,
                                                SluggerInterface $slugger, MailerInterface $mailer): Response
    {


        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!($this->isGranted('ROLE_STUDENT') || $this->isGranted('ROLE_PROFESSIONAL')))
            return $this->redirectToRoute('homepage');

        $entityManager = $doctrine->getManager();
        $user = $this->security->getUser();

        $workshopCarts = $entityManager->getRepository(WorkshopCart::class)
            ->findBy(['selfcareUer' => $user, 'status' => 0]);
        $total = 0;

        if (!$workshopCarts) {
            return $this->render('selfcare/404.html.twig', ['reference' => '']);
        }

        foreach ($workshopCarts as $workshopCart) {
            $workshopPrice = intval($workshopCart->getWorkshop()->getPrice());
            if (!($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles())))) {

                $total += number_format(floatval(str_replace(',', '.', $workshopCart->getWorkshop()->getPrice())), 3, '.', '');
            }

//            $total += number_format(floatval(str_replace(',', '.', $workshopPrice)), 3, '.', '');
        }


        if ($request->getMethod() == 'POST') {

            $paymentFile = new CartFile();
            $paymentFile->setFile($request->files->get('proof'));
            $paymentBank = $request->get('paymentBank');
            $paymentFile->setStatus('0');
            $entityManager->persist($paymentFile);
            $entityManager->flush();
            $dateNow = new \DateTime();
            $dateNowF = $dateNow->format('Y/m/d H:i:s');

            foreach ($workshopCarts as $workshopCart) {
                $workshopCart->setIsFree(false);
                if ($workshopCart->getWorkshop()->getWorkShopType() == '2' &&
                    is_int(array_search('ROLE_STUDENT', $workshopCart->getSelfcareUer()->getRoles()))) {
                    // workshop  is free for student Honoris
                    $workshopCart->setIsFree(true);

                }
                $workshopCart->setStatus('1');
                $workshopCart->setExpired('0');
                $workshopCart->setFile($paymentFile);
                $workshopCart->setPayedAt($dateNowF);
                $workshopCart->setPaymentBank($paymentBank);
                $workshopCart->setPaymentMode('2');
                $workshopCart->setPaymentAmount($workshopCart->getWorkshop()->getPrice());
            }
            $entityManager->flush();
            $adminEmails = explode(",", $_ENV['EMAIL_ADMIN']);
            $email = (new TemplatedEmail())
                ->from('courses.honorismedicalsimulation@universitecentrale.tn')
                ->to(...$adminEmails)
                ->subject('Approval File')
                ->htmlTemplate('emails/approved_email.html.twig');
            $mailer->send($email);

            return $this->redirectToRoute('my_learning');
        }

        return $this->render('selfcare/confirm_bank_tranfer.html.twig', [
            'total' => $total,
            'workshopCarts' => $workshopCarts,
        ]);
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

}
