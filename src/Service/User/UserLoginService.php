<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\AuthenticationToken;
use App\Entity\User;
use App\Entity\UserBanHistory;
use App\Entity\UserInformation;
use App\Enums\BanPeriodRage;
use App\Enums\UserBanType;
use App\Enums\UserLogin;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\PermissionException;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\UserBanHistoryRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\Service\TranslateService;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

class UserLoginService
{
    public function __construct(
        private readonly UserInformationRepository $userInformationRepository,
        private readonly UserPasswordRepository $userPasswordRepository,
        private readonly AuthenticationTokenRepository $authenticationTokenRepository,
        private readonly TranslateService $translateService,
        private readonly UserBanHistoryRepository $banHistoryRepository,
        private readonly UserRepository $userRepository,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function getUserInformation(string $email, Request $request): UserInformation
    {
        $userInformation = $this->userInformationRepository->findOneBy([
            'email' => $email,
        ]);

        if ($userInformation === null) {
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('EmailDontExists')]);
        }

        return $userInformation;
    }

    public function getValidUser(UserInformation $userInformation, Request $request): User
    {
        $user = $userInformation->getUser();

        if ($user->isBanned()) {
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserBanned')]);
        }

        if (!$user->isActive()) {
            $this->translateService->setPreferredLanguage($request);
            throw new DataNotFoundException([$this->translateService->getTranslation('ActivateAccount')]);
        }

        $roles = $user->getRoles();
        $isUser = false;

        foreach ($roles as $role) {
            if ($role->getName() !== UserRolesNames::GUEST->value) {
                $isUser = true;
                break;
            }
        }

        if (!$isUser) {
            throw new PermissionException();
        }

        return $user;
    }

    public function loginToService(UserInformation $userInformation, Request $request, string $password): void
    {
        $user = $userInformation->getUser();
        $passwordHashGenerator = new PasswordHashGenerator($password);

        $passwordEntity = $this->userPasswordRepository->findOneBy([
            'user'     => $user->getId(),
            'password' => $passwordHashGenerator->generate(),
        ]);

        if ($passwordEntity === null) {
            $this->checkLoginAttempts($userInformation, $request);
        }
    }

    private function checkLoginAttempts(UserInformation $userInformation, Request $request): void
    {
        $user = $userInformation->getUser();
        $loginAttempts = $userInformation->getLoginAttempts();

        if ($loginAttempts < UserLogin::MAX_LOGIN_ATTEMPTS->value) {
            $this->addLoginAttempt($userInformation, $loginAttempts);
            throw new DataNotFoundException([$this->translateService->getTranslation('NotActivePassword')]);
        }

        $this->resetLoginAttempts($userInformation);

        $banPeriod = (new DateTime())->modify(BanPeriodRage::HOUR_BAN->value);

        $banHistory = new UserBanHistory($user, new DateTime(), $banPeriod, UserBanType::MAX_LOGINS_BREAK);
        $this->banHistoryRepository->add($banHistory);

        $user
            ->setBanned(true)
            ->setBannedTo($banPeriod);

        $this->userRepository->add($user);

        $email = (new TemplatedEmail())
            ->from($_ENV['INSTITUTION_EMAIL'])
            ->to($userInformation->getEmail())
            ->subject($this->translateService->getTranslation('MaxLoginsMailAttempts'))
            ->htmlTemplate('emails/userBanTooManyLoginsAttempts.html.twig')
            ->context([
                'userName'  => $user->getUserInformation()->getFirstname() . ' ' . $user->getUserInformation()->getLastname(),
                'banPeriod' => BanPeriodRage::HOUR_BAN->value,
                'lang'      => $request->getPreferredLanguage() !== null ? $request->getPreferredLanguage() : $this->translateService->getLocate(),
            ]);
        $this->mailer->send($email);

        $this->translateService->setPreferredLanguage($request);
        throw new DataNotFoundException([$this->translateService->getTranslation('BannedForBreakMaxLogins')]);
    }

    public function getAuthenticationToken($user): AuthenticationToken
    {
        $authenticationToken = $this->authenticationTokenRepository->getLastActiveUserAuthenticationToken($user);

        if ($authenticationToken === null) {
            $authTokenGenerator = new AuthTokenGenerator($user);
            $authenticationToken = new AuthenticationToken($user, $authTokenGenerator);
            $this->authenticationTokenRepository->add($authenticationToken);
        }

        return $authenticationToken;
    }

    public function resetLoginAttempts(UserInformation $userInformation): void
    {
        $userInformation->setLoginAttempts(0);
        $this->userInformationRepository->add($userInformation);
    }

    private function addLoginAttempt(UserInformation $userInformation, int $loginAttempts): void
    {
        $userInformation->setLoginAttempts($loginAttempts + 1);
        $this->userInformationRepository->add($userInformation);
    }
}
