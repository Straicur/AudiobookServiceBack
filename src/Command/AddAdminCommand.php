<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Enums\UserRolesNames;
use App\Repository\InstitutionRepository;
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
    name       : 'audiobookservice:admin:add',
    description: 'Add user to service',
)]
class AddAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly UserSettingsRepository $userSettingsRepository,
        private readonly MyListRepository $myListRepository,
        private readonly InstitutionRepository $institutionRepository,
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $email = $input->getArgument('email');
        $phone = $input->getArgument('phone');
        $password = md5($input->getArgument('password'));

        $institution = $this->institutionRepository->findOneBy([
            'name' => $_ENV['INSTITUTION_NAME'],
        ]);

        $administrator = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::ADMINISTRATOR->value,
        ]);

        if ($institution->getMaxAdmins() < count($this->userRepository->getUsersByRole($administrator))) {
            $io->info('To much admins');
            return Command::FAILURE;
        }

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

        $passwordGenerator = new PasswordHashGenerator($password);

        $io->text([
            'Firstname:    ' . $firstname,
            'Lastname:     ' . $lastname,
            'E-mail:       ' . $email,
            'Phone number: ' . $phone,
            'Password:     ' . str_repeat('*', strlen($password)),
        ]);

        $userEntity = new User();

        $userEntity->setActive(true);

        $this->userRepository->add($userEntity, false);

        $roles = [UserRolesNames::ADMINISTRATOR->value, UserRolesNames::USER->value, UserRolesNames::GUEST->value];

        $roleEntities = $this->roleRepository->findBy([
            'name' => $roles,
        ]);

        $isAdministrator = false;

        foreach ($roleEntities as $roleEntity) {
            if ($roleEntity->getName() === UserRolesNames::ADMINISTRATOR->value) {
                $isAdministrator = true;
            }

            $userEntity->addRole($roleEntity);
        }

        $this->myListRepository->add(new MyList($userEntity), false);
        $this->proposedAudiobooksRepository->add(new ProposedAudiobooks($userEntity), false);
        $this->userInformationRepository->add(new UserInformation($userEntity, $email, $phone, $firstname, $lastname), false);
        $userSettingsEntity = new UserSettings($userEntity);

        if ($isAdministrator) {
            $userSettingsEntity->setAdmin(true);
        }

        $this->userSettingsRepository->add($userSettingsEntity, false);
        $this->userPasswordRepository->add(new UserPassword($userEntity, $passwordGenerator));

        $io->info('Database flushed');

        $io->text([
            'UserEntity:            ' . $userEntity->getId(),
        ]);

        $io->success('Admin user added');

        return Command::SUCCESS;
    }
}
