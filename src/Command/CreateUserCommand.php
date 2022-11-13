<?php

namespace App\Command;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Exception\DataNotFoundException;
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

/**
 * CreateUserCommand
 *
 */
#[AsCommand(
    name: 'audiobookservice:users:create',
    description: 'Add user to service',
)]
class CreateUserCommand extends Command
{
    private UserRepository $userRepository;

    private RoleRepository $roleRepository;

    private UserInformationRepository $userInformationRepository;

    private UserPasswordRepository $userPasswordRepository;

    private UserSettingsRepository $userSettingsRepository;

    private MyListRepository $myListRepository;

    private ProposedAudiobooksRepository $proposedAudiobooksRepository;

    public function __construct(
        UserRepository               $userRepository,
        RoleRepository               $roleRepository,
        UserInformationRepository    $userInformationRepository,
        UserPasswordRepository       $userPasswordRepository,
        UserSettingsRepository       $userSettingsRepository,
        MyListRepository             $myListRepository,
        ProposedAudiobooksRepository $proposedAudiobooksRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordRepository = $userPasswordRepository;
        $this->roleRepository = $roleRepository;
        $this->userInformationRepository = $userInformationRepository;
        $this->userSettingsRepository = $userSettingsRepository;
        $this->myListRepository = $myListRepository;
        $this->proposedAudiobooksRepository = $proposedAudiobooksRepository;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $firstname = $input->getArgument("firstname");
        $lastname = $input->getArgument("lastname");
        $email = $input->getArgument("email");
        $phone = $input->getArgument("phone");
        $password = $input->getArgument("password");
        $roles = $input->getArgument("roles");

        $passwordGenerator = new PasswordHashGenerator($password);

        $io->text([
            "Firstname:    " . $firstname,
            "Lastname:     " . $lastname,
            "E-mail:       " . $email,
            "Phone number: " . $phone,
            "Password:     " . str_repeat("*", strlen($password)),
            "System roles: " . implode(",", $roles),
        ]);

        $userEntity = new User();

        $this->userRepository->add($userEntity, false);

        $roleEntities = $this->roleRepository->findBy([
            "name" => $roles
        ]);

        foreach ($roleEntities as $roleEntity) {
            $userEntity->addRole($roleEntity);
        }

        $userMyList = new MyList($userEntity);

        $this->myListRepository->add($userMyList);

        $userProposedAudiobooks = new ProposedAudiobooks($userEntity);

        $this->proposedAudiobooksRepository->add($userProposedAudiobooks);

        $userInformationEntity = new UserInformation($userEntity, $email, $phone, $firstname, $lastname);

        $this->userInformationRepository->add($userInformationEntity, false);

        $userSettingsEntity = new UserSettings($userEntity);

        $this->userSettingsRepository->add($userSettingsEntity, false);

        $userPasswordEntity = new UserPassword($userEntity, $passwordGenerator);
        $this->userPasswordRepository->add($userPasswordEntity);

        $io->info("Database flushed");

        $io->text([
            "UserEntity:            " . $userEntity->getId(),
            "UserInformationEntity: " . $userInformationEntity->getUser()->getId(),
            "UserSettingEntity:     " . $userSettingsEntity->getUser()->getId(),
            "UserPasswordEntity:    " . $userPasswordEntity->getUser()->getId()
        ]);

        $io->success('User added');

        return Command::SUCCESS;
    }
}
