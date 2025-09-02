<?php

namespace App\Command;

use App\Entity\Workshop;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Translatable\Entity\Translation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:translate-workshop',
    description: 'Ajoute des traductions pour un Workshop donné.',
)]
class TranslateWorkshopCommand extends Command
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
        $workshopId = $input->getArgument('id');

        /** @var Workshop|null $workshop */
        $workshop = $this->em->getRepository(Workshop::class)->find($workshopId);

        if (!$workshop) {
            $output->writeln("<error>Workshop avec ID $workshopId introuvable.</error>");
            return Command::FAILURE;
        }

        /** @var \Gedmo\Translatable\Entity\TranslationRepository $translationRepo */
        $translationRepo = $this->em->getRepository(Translation::class);

        // Exemple : traduire vers le français
        $translationRepo->translate($workshop, 'name', 'fr', 'L’induction à séquence lente');
        $translationRepo->translate($workshop, 'description', 'fr', "Pratique d’une induction à séquence lente");
        $translationRepo->translate($workshop, 'videoTitle', 'fr', "Induction à séquence lente");
//        $translationRepo->translate($workshop, 'workshopAbstract', 'fr', 'Résumé en français');

        $this->em->flush();

        $output->writeln("<info>Traductions ajoutées avec succès pour le Workshop ID $workshopId.</info>");
        return Command::SUCCESS;
    }
}
