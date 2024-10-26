<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Repository\MyListRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use App\ValueGenerator\PasswordHashGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name       : 'audiobookservice:users:create',
    description: 'Add user to service',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly UserSettingsRepository $userSettingsRepository,
        private readonly MyListRepository $myListRepository,
        private readonly ProposedAudiobooksRepository $proposedAudiobooksRepository,
    ) {
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $email = $input->getArgument('email');
        $phone = $input->getArgument('phone');
        $password = md5($input->getArgument('password'));
        $roles = $input->getArgument('roles');

        $passwordGenerator = new PasswordHashGenerator($password);

        $io->text([
            'Firstname:    ' . $firstname,
            'Lastname:     ' . $lastname,
            'E-mail:       ' . $email,
            'Phone number: ' . $phone,
            'Password:     ' . str_repeat('*', strlen($password)),
            'System roles: ' . implode(',', $roles),
        ]);

        $existingEmail = $this->userInformationRepository->findOneBy([
            'email' => $email,
        ]);

        if ($existingEmail !== null) {
            $io->error('Email exists');
            return Command::FAILURE;
        }

        $existingPhone = $this->userInformationRepository->findOneBy([
            'phoneNumber' => $phone,
        ]);

        if ($existingPhone !== null) {
            $io->error('PhoneNumber exists');
            return Command::FAILURE;
        }

        $userEntity = new User();

        $userEntity->setActive(true);

        $this->userRepository->add($userEntity, false);

        $roleEntities = $this->roleRepository->findBy([
            'name' => $roles,
        ]);

        foreach ($roleEntities as $roleEntity) {
            $userEntity->addRole($roleEntity);
        }

        $this->myListRepository->add(new MyList($userEntity), false);
        $this->proposedAudiobooksRepository->add(new ProposedAudiobooks($userEntity), false);
        $this->userInformationRepository->add(new UserInformation($userEntity, $email, $phone, $firstname, $lastname), false);
        $this->userSettingsRepository->add(new UserSettings($userEntity), false);
        $this->userPasswordRepository->add(new UserPassword($userEntity, $passwordGenerator));

        $io->info('Database flushed');

        $io->text([
            'UserEntity:            ' . $userEntity->getId(),
        ]);

        $io->success('User added');

        return Command::SUCCESS;
    }
}
