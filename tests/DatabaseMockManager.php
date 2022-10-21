<?php

namespace App\Tests;

use App\Entity\AuthenticationToken;
use App\Entity\Institution;
use App\Entity\RegisterCode;
use App\Entity\User;
use App\Entity\UserInformation;
use App\Entity\UserPassword;
use App\Entity\UserSettings;
use App\Repository\AuthenticationTokenRepository;
use App\Repository\InstitutionRepository;
use App\Repository\RegisterCodeRepository;
use App\Repository\RoleRepository;
use App\Repository\UserPasswordRepository;
use App\Repository\UserRepository;
use App\ValueGenerator\AuthTokenGenerator;
use App\ValueGenerator\PasswordHashGenerator;
use App\ValueGenerator\RegisterCodeGenerator;
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

    public function testFunc_addUser(string $firstname, string $lastname, string $email, string $phone, array $rolesNames = [], bool $mainGroup = false, string $password = null, bool $insideParkName = null, bool $banned = false, bool $notActive = false): User
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
        if ($notActive) {
            $user->setActive(false);
        }
        else{
            $user->setActive(true);
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

    public function testFunc_addRegisterCode(User $user, \DateTime $dateAccept = null, bool $active = false, string $code = null): RegisterCode
    {
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        if ($code) {
            $registerCodeGenerator = new RegisterCodeGenerator($code);
        }
        else{
            $registerCodeGenerator = new RegisterCodeGenerator();
        }

        $newRegisterCode = new RegisterCode($registerCodeGenerator, $user);

        if ($dateAccept != null) {
            $newRegisterCode->setDateAccept($dateAccept);
        }
        if ($active) {
            $newRegisterCode->setActive($active);
        }

        $registerCodeRepository->add($newRegisterCode);

        return $newRegisterCode;
    }

    public function testFunc_getInstitution(): Institution
    {
        $institutionRepository = $this->getService(InstitutionRepository::class);

        return $institutionRepository->findOneBy([
            "name"=>$_ENV["INSTITUTION_NAME"]
        ]);
    }
}