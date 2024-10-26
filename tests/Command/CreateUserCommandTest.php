<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Repository\UserInformationRepository;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends AbstractKernelTestCase
{
    public function testCreateUserCommandSuccess(): void
    {
        $userInformationRepository = $this->getService(UserInformationRepository::class);

        $this->assertInstanceOf(UserInformationRepository::class, $userInformationRepository);

        $cmd = $this->commandApplication->find('audiobookservice:users:create');

        $tester = new CommandTester($cmd);

        $tester->execute([
            'firstname' => 'Damian',
            'lastname'  => 'Mosiński',
            'email'     => 'mosinskidamiantest@gmail.com',
            'phone'     => '920921223',
            'password'  => 'zaq12wsx',
            'roles'     => ['User'],
        ]);

        $tester->assertCommandIsSuccessful();

        $userInformationFound = $userInformationRepository->findOneBy(['email' => 'mosinskidamiantest@gmail.com']);

        $this->assertNotNull($userInformationFound);
    }
}
