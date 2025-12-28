<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddCategoryCommandTest extends AbstractKernelTestCase
{
    public function testAddCategoryCommandSuccess(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);

        $cmd = $this->commandApplication->find('audiobookservice:category:add');

        $tester = new CommandTester($cmd);

        $tester->execute(['name' => 'Test']);

        $tester->assertCommandIsSuccessful();

        $audiobookCategoryFound = $audiobookCategoryRepository->findOneBy(['name' => 'Test']);

        $this->assertNotNull($audiobookCategoryFound);
    }
}
