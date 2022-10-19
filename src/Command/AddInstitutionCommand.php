<?php

namespace App\Command;

use App\Exception\DataNotFoundException;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CreateUserCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:users:create',
    description: 'Add user to service',
)]
class AddInstitutionCommand extends Command
{
    private UserRepository $userRepository;

    private RoleRepository $roleRepository;

    private UserInformationRepository $userInformationRepository;

    private UserPasswordRepository $userPasswordRepository;

    private UserSettingsRepository $userSettingsRepository;


    public function __construct(
        UserRepository            $userRepository,
        RoleRepository            $roleRepository,
        UserInformationRepository $userInformationRepository,
        UserPasswordRepository    $userPasswordRepository,
        UserSettingsRepository    $userSettingsRepository,

    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordRepository = $userPasswordRepository;
        $this->roleRepository = $roleRepository;
        $this->userInformationRepository = $userInformationRepository;
        $this->userSettingsRepository = $userSettingsRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('firstname', InputArgument::REQUIRED, 'User firstname');
        $this->addArgument('lastname', InputArgument::REQUIRED, 'User lastname');
        $this->addArgument('email', InputArgument::REQUIRED, 'User e-mail address');
        $this->addArgument('phone', InputArgument::REQUIRED, 'User phone number');
        $this->addArgument('password', InputArgument::REQUIRED, 'User password');
        $this->addArgument('roles', InputArgument::IS_ARRAY, 'User roles');
    }

    /**
     * @throws DataNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Success');

        return Command::SUCCESS;
    }
}