<?php
// src/Command/AddFrenchTranslationsCommand.php (Optionnel - pour ajouter des traductions vides)

namespace App\Command;

use App\Entity\Workshop;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-french-translations',
    description: 'Ajoute des traductions françaises vides pour tous les workshops'
)]
class AddFrenchTranslationsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TranslationService $translationService;

    public function __construct(EntityManagerInterface $entityManager, TranslationService $translationService)
    {
        $this->entityManager = $entityManager;
        $this->translationService = $translationService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Ajout des traductions françaises pour les workshops');

        $workshops = $this->entityManager->getRepository(Workshop::class)->findAll();

        $io->progressStart(count($workshops));

        foreach ($workshops as $workshop) {
            // Ajouter des traductions françaises (vous pouvez les modifier manuellement après)

            if ($workshop->getName() && !$this->translationService->hasTranslation($workshop, 'name', 'fr')) {
                $this->translationService->addTranslation($workshop, 'name', 'fr', '[FR] ' . $workshop->getName());
            }

            if ($workshop->getDescription() && !$this->translationService->hasTranslation($workshop, 'description', 'fr')) {
                $this->translationService->addTranslation($workshop, 'description', 'fr', '[FR] ' . $workshop->getDescription());
            }

            if ($workshop->getVideoTitle() && !$this->translationService->hasTranslation($workshop, 'videoTitle', 'fr')) {
                $this->translationService->addTranslation($workshop, 'videoTitle', 'fr', '[FR] ' . $workshop->getVideoTitle());
            }

            if ($workshop->getWorkshopAbstract() && !$this->translationService->hasTranslation($workshop, 'workshopAbstract', 'fr')) {
                $this->translationService->addTranslation($workshop, 'workshopAbstract', 'fr', '[FR] ' . $workshop->getWorkshopAbstract());
            }

            if ($workshop->getDurationUnit() && !$this->translationService->hasTranslation($workshop, 'durationUnit', 'fr')) {
                // Pour les unités de durée, traduisez directement
                $frenchUnit = $workshop->getDurationUnit() === 'hours' ? 'heures' :
                    ($workshop->getDurationUnit() === 'minutes' ? 'minutes' :
                        ($workshop->getDurationUnit() === 'days' ? 'jours' : $workshop->getDurationUnit()));

                $this->translationService->addTranslation($workshop, 'durationUnit', 'fr', $frenchUnit);
            }

            if ($workshop->getWorkshopEstablishment() && !$this->translationService->hasTranslation($workshop, 'workshopEstablishment', 'fr')) {
                $this->translationService->addTranslation($workshop, 'workshopEstablishment', 'fr', '[FR] ' . $workshop->getWorkshopEstablishment());
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Traductions françaises ajoutées ! N\'oubliez pas de les personnaliser.');

        return Command::SUCCESS;
    }
}