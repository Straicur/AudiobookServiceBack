<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractWebTest extends WebTestCase
{
    protected KernelBrowser $webClient;
    protected ?object $entityManager;
    protected ?DatabaseMockManager $databaseMockManager = null;
    protected ?TestTool $responseTool = null;
    protected string $mainDir = '';

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->webClient = static::createClient(['environment' => 'test']);
        $this->webClient->enableProfiler();

        $this->databaseMockManager = new DatabaseMockManager(static::getContainer());
        $this->responseTool = new TestTool('AbstractWebTest');

        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager->getConnection()->beginTransaction();

        $this->mainDir = self::getContainer()->getParameter('app.main_dir');
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollback();
        }

        $application = new Application();
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:pool:clear',
            '--all' => true,
        ]);
        $application->run($input, new BufferedOutput());

        $input = new ArrayInput([
            'command' => 'doctrine:cache:clear-metadata'
        ]);
        $application->run($input, new BufferedOutput());

    }

    protected function getService(string $serviceName): object
    {
        return $this->webClient->getContainer()->get($serviceName);
    }
}
