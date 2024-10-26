<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddRoleCommandTest extends AbstractKernelTestCase
{
    public function testAddRoleCommandSuccess(): void
    {
        $roleRepository = $this->getService(RoleRepository::class);

        $this->assertInstanceOf(RoleRepository::class, $roleRepository);

        $cmd = $this->commandApplication->find('audiobookservice:roles:add');

        $tester = new CommandTester($cmd);

        $tester->execute(['roleName' => 'Test']);

        $tester->assertCommandIsSuccessful();

        $roleFound = $roleRepository->findOneBy(['name' => 'Test']);

        $this->assertNotNull($roleFound);
    }
}
