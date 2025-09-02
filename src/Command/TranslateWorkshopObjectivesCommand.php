<?php

namespace App\Command;

use App\Entity\Translation\WorkshopTranslation;
use App\Entity\WorkShopObjectives;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:translate-workshop-objectives')]
class TranslateWorkshopObjectivesCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'ID du Workshop à traduire');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $objectiveId = $input->getArgument('id');
        $repo = $this->em->getRepository(WorkShopObjectives::class);
        $translationRepo = $this->em->getRepository(Translation::class);

        $objective = $this->em->getRepository(WorkShopObjectives::class)->find($objectiveId);

        if (!$objective) {
            $output->writeln("<error>Workshop avec ID $objectiveId introuvable.</error>");
            return Command::FAILURE;
        }
        $output->writeln("Translating objective ID: {$objective->getId()}");

        $translationRepo->translate($objective, 'name', 'fr', "Faire face aux incidents possibles lors d`une induction anesthésique.");
        $translationRepo->translate($objective, 'description', 'fr', "Faire face aux incidents possibles lors d`une induction anesthésique.");


        $this->em->flush();

        $output->writeln("Traductions enregistrées avec succès.");
        return Command::SUCCESS;
    }
}
