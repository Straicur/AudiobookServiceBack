<?php

namespace App\Command;

use App\Entity\Institution;
use App\Repository\InstitutionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * AddInstitutionCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:institution:add',
    description: 'Add user to service',
)]
class AddInstitutionCommand extends Command
{
    private InstitutionRepository $institutionRepository;

    public function __construct(
        InstitutionRepository $institutionRepository,
    )
    {
        $this->institutionRepository = $institutionRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('phoneNumber', InputArgument::REQUIRED, 'Institution phoneNumber');
        $this->addArgument('maxAdmins', InputArgument::REQUIRED, 'Institution max number of admins');
        $this->addArgument('maxUsers', InputArgument::REQUIRED, 'Institution max number of users');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phoneNumber = $input->getArgument("phoneNumber");
        $maxAdmins = $input->getArgument("maxAdmins");
        $maxUsers = $input->getArgument("maxUsers");


        $io->text([
            "PhoneNumber:  " . $phoneNumber,
            "MaxAdmins:    " . $maxAdmins,
            "MaxUsers:     " . $maxUsers,
        ]);

        if (count($this->institutionRepository->findAll()) > 0) {
            return Command::FAILURE;
        }

        $this->institutionRepository->add(new Institution($_ENV["INSTITUTION_NAME"], $_ENV["INSTITUTION_EMAIL"], $phoneNumber, $maxAdmins, $maxUsers));

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');

        return Command::SUCCESS;
    }
}