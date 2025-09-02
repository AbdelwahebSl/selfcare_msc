<?php
// src/Command/TranslateExistingDataCommand.php

namespace App\Command;

use App\Entity\Workshop;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslateExistingDataCommand extends Command
{
    protected static $defaultName = 'app:translate-existing-data';

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
        $articles = $this->entityManager->getRepository(Workshop::class)->findAll();

        foreach ($articles as $article) {
            // Marquer les données existantes comme étant en anglais
            $this->translationService->translateEntity($article, 'en');

            // Vous pouvez ajouter ici la logique pour créer des traductions automatiques
            // ou préparer les champs pour traduction manuelle
        }

        $this->entityManager->flush();

        $output->writeln('Migration completed!');
        return Command::SUCCESS;
    }
}