<?php

namespace App\Tests;

use App\Entity\AuthenticationToken;
use App\Entity\TenzieResult;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\RoleRepository;
use App\Repository\TenzieResultRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use Symfony\Component\HttpKernel\KernelInterface;

class DatabaseMockManager
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    protected function getService(string $serviceName): object
    {
        return $this->kernel->getContainer()->get($serviceName);
    }

    private function testFunc_addRole(User $user, array $rolesNames): void
    {
        $roleRepository = $this->getService(RoleRepository::class);

        $roles = $roleRepository->findBy([
            "name" => $rolesNames
        ]);

        foreach ($roles as $role) {
            $role->addUser($user);
            $roleRepository->add($role);
        }
    }

    public function testFunc_addUser(string $firstname, string $lastname, string $email, string $phone, array $rolesNames = [], bool $mainGroup = false, string $password = null, bool $insideParkName = null, bool $banned = false, bool $landlord = null, array $userSettings = [], string $bankAccount = null): User
    {
        $userRepository = $this->getService(UserRepository::class);
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $user = new User();
        $userRepository->add($user);

        $userInformation = new UserInformation($user, $email, $phone, $firstname, $lastname);

        $user->setUserSettings(new UserSettings($user));
        $user->setUserInformation($userInformation);

        if ($banned) {
            $user->setBanned(true);
        }

        $userRepository->add($user);

        $userRepository->add($user);

        $this->testFunc_addRole($user, $rolesNames);

        if ($password != null) {
            $userPassword = new UserPassword($user, new PasswordHashGenerator($password));
            $userPasswordRepository->add($userPassword);
        }

        return $userRepository->findOneBy(["id" => $user->getId()]);
    }

    public function testFunc_loginUser(User $user): AuthenticationToken
    {
        $authenticationTokenRepository = $this->getService(AuthenticationTokenRepository::class);

        $authenticationToken = new AuthenticationToken($user, new AuthTokenGenerator($user));

        $authenticationTokenRepository->add($authenticationToken);

        return $authenticationTokenRepository->findOneBy(["id" => $authenticationToken->getId()]);
    }

    public function testFunc_addTenzieResult(User $user, int $level, string $title, string $time, bool $deleted = false): TenzieResult
    {
        $tenzieResultRepository = $this->getService(TenzieResultRepository::class);

        $newTenzieResult = new TenzieResult($user, $title, $level, $time);

        if ($deleted) {
            $newTenzieResult->setDeleted(true);
        }

        $tenzieResultRepository->add($newTenzieResult);

        return $newTenzieResult;
    }
}