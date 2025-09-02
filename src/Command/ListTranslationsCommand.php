<?php
// src/Command/ListTranslationsCommand.php

namespace App\Command;

use App\Entity\Workshop;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-translations',
    description: 'Liste les traductions existantes pour un workshop'
)]
class ListTranslationsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TranslationRepository $translationRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->translationRepository = $entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('workshop_id', InputArgument::OPTIONAL, 'ID du workshop (optionnel)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $workshopId = $input->getArgument('workshop_id');

        if ($workshopId) {
            $workshop = $this->entityManager->getRepository(Workshop::class)->find($workshopId);
            if (!$workshop) {
                $io->error("Workshop avec l'ID {$workshopId} introuvable.");
                return Command::FAILURE;
            }
            $this->displayWorkshopTranslations($io, $workshop);
        } else {
            $workshops = $this->entityManager->getRepository(Workshop::class)->findAll();
            foreach ($workshops as $workshop) {
                $this->displayWorkshopTranslations($io, $workshop);
                $io->newLine();
            }
        }

        return Command::SUCCESS;
    }

    private function displayWorkshopTranslations(SymfonyStyle $io, Workshop $workshop): void
    {
        $translations = $this->translationRepository->findTranslations($workshop);

        $io->section("Workshop ID: {$workshop->getId()} - {$workshop->getName()}");

        if (empty($translations)) {
            $io->note('Aucune traduction trouvée.');
            return;
        }

        $tableData = [];
        foreach ($translations as $locale => $fields) {
            foreach ($fields as $field => $value) {
                $tableData[] = [$locale, $field, substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '')];
            }
        }

        $io->table(['Locale', 'Champ', 'Valeur'], $tableData);
    }
}