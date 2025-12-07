<?php

declare(strict_types = 1);

namespace App\Service\User;

use App\Entity\MyList;
use App\Entity\ProposedAudiobooks;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Query\Common\RegisterQuery;
use App\Repository\InstitutionRepository;
use App\Repository\MyListRepository;
use App\Repository\ProposedAudiobooksRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Repository\UserSettingsRepository;
use App\Service\TranslateServiceInterface;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\RegisterCodeGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

use function count;

class UserRegisterService implements UserRegisterServiceInterface
{
    public function __construct(
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $endpointLogger,
        private readonly RegisterCodeRepository $registerCodeRepository,
        private readonly MailerInterface $mailer,
        private readonly RoleRepository $roleRepository,
        private readonly MyListRepository $myListRepository,
        private readonly ProposedAudiobooksRepository $proposedAudiobooksRepository,
        private readonly InstitutionRepository $institutionRepository,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly TranslateServiceInterface $translateService,
        private readonly UserSettingsRepository $userSettingsRepository,
    ) {}

    public function checkExistingUsers(RegisterQuery $registerQuery, Request $request): void
    {
        $existingEmail = $this->userInformationRepository->findOneBy([
            'email' => $registerQuery->getEmail(),
        ]);

        if (null !== $existingEmail) {
            $this->endpointLogger->error('Email already exists');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('EmailExists')]);
        }

        $existingPhone = $this->userInformationRepository->findOneBy([
            'phoneNumber' => $registerQuery->getPhoneNumber(),
        ]);

        if (null !== $existingPhone) {
            $this->endpointLogger->error('Phone number already exists');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('PhoneNumberExists')]);
        }
    }

    public function checkInstitutionLimits(Request $request): void
    {
        $institution = $this->institutionRepository->findOneBy([
            'name' => $_ENV['INSTITUTION_NAME'],
        ]);

        $guest = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::GUEST->value,
        ]);

        if ($institution->getMaxUsers() < count($this->userRepository->getUsersByRole($guest))) {
            $this->endpointLogger->error('Too much users');
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('ToMuchUsers')]);
        }
    }

    public function createUser(RegisterQuery $registerQuery): User
    {
        $newUser = new User();

        $this->userRepository->add($newUser, false);
        $this->userSettingsRepository->add(new UserSettings($newUser), false);
        $this->myListRepository->add(new MyList($newUser), false);
        $this->proposedAudiobooksRepository->add(new ProposedAudiobooks($newUser), false);

        $newUserInformation = new UserInformation(
            $newUser,
            $registerQuery->getEmail(),
            $registerQuery->getPhoneNumber(),
            $registerQuery->getFirstname(),
            $registerQuery->getLastname(),
        );

        $additionalData = $registerQuery->getAdditionalData();
        $birthday = $additionalData['birthday'] ?? null;

        if (null !== $birthday) {
            $newUserInformation->setBirthday($birthday);
        }

        $newUser->setUserInformation($newUserInformation);

        $this->addGuestRole($newUser);
        $this->createPassword($newUser, $registerQuery->getPassword());

        return $newUser;
    }

    private function addGuestRole(User $newUser): void
    {
        $guestRole = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::GUEST->value,
        ]);

        $newUser->addRole($guestRole);
    }

    private function createPassword(User $newUser, string $newPassword): void
    {
        $passwordGenerator = new PasswordHashGenerator($newPassword);

        $userPasswordEntity = new UserPassword($newUser, $passwordGenerator);

        $this->userPasswordRepository->add($userPasswordEntity);
    }

    public function getRegisterCode(User $newUser): string
    {
        $registerCodeGenerator = new RegisterCodeGenerator();

        $registerCode = new RegisterCode($registerCodeGenerator, $newUser);

        $this->registerCodeRepository->add($registerCode);

        return $registerCodeGenerator->getBeforeGenerate();
    }

    public function sendMail(User $newUser, string $registerCode, Request $request): void
    {
        if ('test' !== $_ENV['APP_ENV']) {
            $email = new TemplatedEmail()
                ->from($_ENV['INSTITUTION_EMAIL'])
                ->to($newUser->getUserInformation()->getEmail())
                ->subject($this->translateService->getTranslation('AccountActivationCodeSubject'))
                ->htmlTemplate('emails/register.html.twig')
                ->context([
                    'userName'  => $newUser->getUserInformation()->getFirstname() . ' ' . $newUser->getUserInformation()->getLastname(),
                    'code'      => $registerCode,
                    'userEmail' => $newUser->getUserInformation()->getEmail(),
                    'url'       => $_ENV['BACKEND_URL'],
                    'lang'      => $request->getPreferredLanguage() ?? $this->translateService->getLocate(),
                ]);

            $this->mailer->send($email);
        }
    }
}
