<?php

namespace App\Twig;

use App\Service\AutoTranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TranslationExtension extends AbstractExtension
{
    public function __construct(
        private   AutoTranslationService $translationService,
        private   RequestStack $requestStack,
        private   UrlGeneratorInterface $urlGenerator
    ) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('auto_trans', [$this, 'autoTranslate']),
            new TwigFilter('trans_html', [$this, 'translateHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('auto_translate', [$this, 'autoTranslate']),
            new TwigFunction('locale_url', [$this, 'getLocaleUrl']),
            new TwigFunction('current_locale', [$this, 'getCurrentLocale']),
            new TwigFunction('available_locales', [$this, 'getAvailableLocales']),
        ];
    }

    public function autoTranslate(string $text, ?string $targetLang = null): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentLocale = $request?->getLocale() ?? 'en';

        // Si la locale actuelle est anglais, pas besoin de traduire
        if ($currentLocale === 'en') {
            return $text;
        }

        $targetLang = $targetLang ?? $currentLocale;

        return $this->translationService->translate($text, $targetLang, 'en');
    }

    public function translateHtml(string $html, ?string $targetLang = null): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentLocale = $request?->getLocale() ?? 'en';

        if ($currentLocale === 'en') {
            return $html;
        }

        $targetLang = $targetLang ?? $currentLocale;

        // Traduire le contenu HTML
        $translatedHtml = $this->translationService->translateHtmlContent($html, $targetLang, 'en');

        // Traduire les attributs
        return $this->translationService->translateAttributes($translatedHtml, $targetLang, 'en');
    }

    public function getLocaleUrl(string $locale): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return "/$locale/";
        }

        $route = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params', []);
        $routeParams['_locale'] = $locale;

        try {
            return $this->urlGenerator->generate($route, $routeParams);
        } catch (\Exception $e) {
            // Fallback si la route n'existe pas
            return "/$locale" . $request->getPathInfo();
        }
    }

    public function getCurrentLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request?->getLocale() ?? 'en';
    }

    public function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'fr' => 'Français'
        ];
    }
}