<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Institution;
use App\Repository\InstitutionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'audiobookservice:institution:add',
    description: 'Add user to service',
)]
class AddInstitutionCommand extends Command
{
    public function __construct(
        private readonly InstitutionRepository $institutionRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('phoneNumber', InputArgument::REQUIRED, 'Institution phoneNumber');
        $this->addArgument('maxAdmins', InputArgument::REQUIRED, 'Institution max number of admins');
        $this->addArgument('maxUsers', InputArgument::REQUIRED, 'Institution max number of users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phoneNumber = $input->getArgument('phoneNumber');
        $maxAdmins = $input->getArgument('maxAdmins');
        $maxUsers = $input->getArgument('maxUsers');

        $io->text([
            'PhoneNumber:  ' . $phoneNumber,
            'MaxAdmins:    ' . $maxAdmins,
            'MaxUsers:     ' . $maxUsers,
        ]);

        if (count($this->institutionRepository->findAll()) > 0) {
            return Command::FAILURE;
        }

        $this->institutionRepository->add(new Institution($_ENV['INSTITUTION_NAME'], $_ENV['INSTITUTION_EMAIL'], $phoneNumber, (int)$maxAdmins, (int)$maxUsers));

        $io = new SymfonyStyle($input, $output);
        $io->success('Success');

        return Command::SUCCESS;
    }
}
