<?php
namespace App\EventListener;

use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleListener
{
    private $translatableListener;
    private $requestStack;

    public function __construct(TranslatableListener $translatableListener, RequestStack $requestStack)
    {
        $this->translatableListener = $translatableListener;
        $this->requestStack = $requestStack;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $this->translatableListener->setTranslatableLocale($request->getLocale());
    }
}

//
//namespace App\EventListener;
//
//use Gedmo\Translatable\TranslatableListener;
//use Symfony\Component\HttpKernel\Event\RequestEvent;
//use Symfony\Component\HttpKernel\KernelEvents;
//use Symfony\Component\EventDispatcher\EventSubscriberInterface;
//
//class LocaleListener implements EventSubscriberInterface
//{
//    private TranslatableListener $translatableListener;
//
//    public function __construct(TranslatableListener $translatableListener)
//    {
//        $this->translatableListener = $translatableListener;
//    }
//
//    public function onKernelRequest(RequestEvent $event): void
//    {
//        $request = $event->getRequest();
//
//        // Définir la locale par défaut
//        if (!$request->hasPreviousSession()) {
//            return;
//        }
//
//        // Récupérer la locale depuis la requête, la session ou utiliser la locale par défaut
//        $locale = $request->attributes->get('_locale')
//            ?: $request->getSession()->get('_locale', $request->getDefaultLocale());
//
//        $request->setLocale($locale);
//        $request->getSession()->set('_locale', $locale);
//
//        // Configurer le TranslatableListener pour utiliser cette locale
//        $this->translatableListener->setTranslatableLocale($locale);
//    }
//
//    public static function getSubscribedEvents(): array
//    {
//        return [
//            KernelEvents::REQUEST => [['onKernelRequest', 20]],
//        ];
//    }
//}