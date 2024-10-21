<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class TestTool extends TestCase
{
    /**
     * test for 400,401,403 codes
     */
    public function testBadResponseData(KernelBrowser $webClient): void
    {
        $responseContent = $webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }

    /**
     * test for 404 code
     */
    public function testErrorResponseData(KernelBrowser $webClient): void
    {
        $responseContent = $webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }
}
