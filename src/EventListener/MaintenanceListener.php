<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    private $maintenance;
    private $twig;

    public function __construct($maintenance, Environment $twig)
    {
        $this->maintenance = $maintenance;
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $event->setResponse(
            new Response(
                $this->twig->render('sorry.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE
            ));
        $event->stopPropagation();
    }

}