<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class AutoTranslationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Traduit automatiquement un texte avec mise en cache
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'en'): string
    {
        // Si même langue, pas besoin de traduction
        if ($sourceLang === $targetLang) {
            return $text;
        }

        // Clé de cache unique
        $cacheKey = 'translation_' . md5($text . $sourceLang . $targetLang);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text, $targetLang, $sourceLang) {
            $item->expiresAfter(3600 * 24 * 7); // Cache pendant 7 jours

            try {
                // Utiliser l'API LibreTranslate (gratuite)
                $response = $this->httpClient->request('POST', 'https://libretranslate.com/translate', [
                    'json' => [
                        'q' => $text,
                        'source' => $sourceLang,
                        'target' => $targetLang,
                        'format' => 'text'
                    ],
                    'timeout' => 5,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $data = $response->toArray();
                $translatedText = $data['translatedText'] ?? $text;

                $this->logger->info('Translation successful', [
                    'original' => $text,
                    'translated' => $translatedText,
                    'from' => $sourceLang,
                    'to' => $targetLang
                ]);

                return $translatedText;

            } catch (\Exception $e) {
                $this->logger->error('Translation failed', [
                    'error' => $e->getMessage(),
                    'text' => $text,
                    'from' => $sourceLang,
                    'to' => $targetLang
                ]);

                // Fallback : retourner le texte original
                return $text;
            }
        });
    }

    /**
     * Traduit le contenu HTML en préservant les balises
     */
    public function translateHtmlContent(string $html, string $targetLang, string $sourceLang = 'en'): string
    {
        if ($sourceLang === $targetLang) {
            return $html;
        }

        // Pattern pour extraire le texte entre les balises HTML
        $pattern = '/>([^<]+)</';

        return preg_replace_callback($pattern, function($matches) use ($targetLang, $sourceLang) {
            $text = trim($matches[1]);

            // Ignorer les textes vides, numériques ou très courts
            if (empty($text) || is_numeric($text) || strlen($text) < 3) {
                return $matches[0];
            }

            $translated = $this->translate($text, $targetLang, $sourceLang);
            return '>' . $translated . '<';
        }, $html);
    }

    /**
     * Traduit les attributs alt, title, placeholder
     */
    public function translateAttributes(string $html, string $targetLang, string $sourceLang = 'en'): string
    {
        $attributes = ['alt', 'title', 'placeholder'];

        foreach ($attributes as $attr) {
            $pattern = '/' . $attr . '="([^"]+)"/';
            $html = preg_replace_callback($pattern, function($matches) use ($targetLang, $sourceLang) {
                $original = $matches[1];
                $translated = $this->translate($original, $targetLang, $sourceLang);
                return str_replace($original, $translated, $matches[0]);
            }, $html);
        }

        return $html;
    }
}