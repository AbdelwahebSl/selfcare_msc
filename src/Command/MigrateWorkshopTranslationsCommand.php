<?php
// src/Command/MigrateWorkshopTranslationsCommand.php

namespace App\Command;

use App\Entity\Workshop;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-workshop-translations',
    description: 'Migre les données existantes des workshops vers le système de traductions'
)]
class MigrateWorkshopTranslationsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TranslationRepository $translationRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->translationRepository = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Migration des traductions des workshops');

        // Récupérer tous les workshops
        $workshops = $this->entityManager->getRepository(Workshop::class)->findAll();

        if (empty($workshops)) {
            $io->warning('Aucun workshop trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($workshops));

        $processedCount = 0;

        foreach ($workshops as $workshop) {
            try {
                // Vérifier si des traductions existent déjà
                $existingTranslations = $this->translationRepository->findTranslations($workshop);

                if (!empty($existingTranslations)) {
                    $io->note("Workshop ID {$workshop->getId()} a déjà des traductions, ignoré.");
                    $io->progressAdvance();
                    continue;
                }

                // Créer les traductions anglaises (données actuelles)
                $this->createTranslation($workshop, 'name', 'en', $workshop->getName());
                $this->createTranslation($workshop, 'description', 'en', $workshop->getDescription());
                $this->createTranslation($workshop, 'videoTitle', 'en', $workshop->getVideoTitle());
                $this->createTranslation($workshop, 'workshopAbstract', 'en', $workshop->getWorkshopAbstract());
                $this->createTranslation($workshop, 'durationUnit', 'en', $workshop->getDurationUnit());
                $this->createTranslation($workshop, 'workshopEstablishment', 'en', $workshop->getWorkshopEstablishment());

                // Créer des traductions françaises par défaut (à modifier manuellement après)
                $this->createTranslation($workshop, 'name', 'fr', $workshop->getName() . ' (FR)');
                $this->createTranslation($workshop, 'description', 'fr', $workshop->getDescription() . ' (Version française)');
                $this->createTranslation($workshop, 'videoTitle', 'fr', $workshop->getVideoTitle() . ' (FR)');
                $this->createTranslation($workshop, 'workshopAbstract', 'fr', $workshop->getWorkshopAbstract() . ' (FR)');

                // Traduction des unités de durée
                $frenchUnit = $this->translateDurationUnit($workshop->getDurationUnit());
                $this->createTranslation($workshop, 'durationUnit', 'fr', $frenchUnit);

                $this->createTranslation($workshop, 'workshopEstablishment', 'fr', $workshop->getWorkshopEstablishment() . ' (FR)');

                $processedCount++;

            } catch (\Exception $e) {
                $io->error("Erreur lors du traitement du workshop ID {$workshop->getId()}: " . $e->getMessage());
            }

            $io->progressAdvance();
        }

        // Sauvegarder toutes les traductions
        $this->entityManager->flush();

        $io->progressFinish();
        $io->success("Migration terminée ! {$processedCount} workshops traités.");

        $io->note([
            'Prochaines étapes :',
            '1. Vérifiez les traductions créées avec: php bin/console app:list-translations',
            '2. Modifiez les traductions françaises via votre interface d\'administration',
            '3. Testez le changement de langue sur votre site'
        ]);

        return Command::SUCCESS;
    }

    private function createTranslation(Workshop $workshop, string $field, string $locale, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        $this->translationRepository->translate($workshop, $field, $locale, $value);
    }

    private function translateDurationUnit(?string $unit): string
    {
        if (!$unit) return '';

        $translations = [
            'hours' => 'heures',
            'hour' => 'heure',
            'minutes' => 'minutes',
            'minute' => 'minute',
            'days' => 'jours',
            'day' => 'jour',
            'weeks' => 'semaines',
            'week' => 'semaine',
            'months' => 'mois',
            'month' => 'mois'
        ];

        return $translations[strtolower($unit)] ?? $unit;
    }
}