<?php


namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

class TranslationService
{
    private EntityManagerInterface $entityManager;
    private TranslationRepository $translationRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->translationRepository = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
    }

    /**
     * Ajouter une traduction pour une entité
     */
    public function addTranslation(object $entity, string $field, string $locale, string $value): void
    {
        $this->translationRepository->translate($entity, $field, $locale, $value);
        $this->entityManager->flush();
    }

    /**
     * Récupérer toutes les traductions d'une entité
     */
    public function getTranslations(object $entity): array
    {
        return $this->translationRepository->findTranslations($entity);
    }

    /**
     * Vérifier si une traduction existe
     */
    public function hasTranslation(object $entity, string $field, string $locale): bool
    {
        $translations = $this->getTranslations($entity);
        return isset($translations[$locale][$field]);
    }

    /**
     * Supprimer une traduction
     */
    public function removeTranslation(object $entity, string $field, string $locale): void
    {
        $translation = $this->translationRepository->findOneBy([
            'locale' => $locale,
            'field' => $field,
            'objectId' => $entity->getId(),
            'objectClass' => get_class($entity)
        ]);

        if ($translation) {
            $this->entityManager->remove($translation);
            $this->entityManager->flush();
        }
    }
}