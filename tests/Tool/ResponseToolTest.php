<?php

declare(strict_types=1);

namespace App\Tests\Tool;

use App\Model\Error\NotAuthorizeModel;
use App\Tool\ResponseTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ResponseToolTest extends TestCase
{
    private static array $_HEADERS = [
        'Content-Type' => [
            'application/json'
        ]
    ];

    public function testResponseHeaders(): void
    {
        $response = ResponseTool::getResponse();

        foreach (self::$_HEADERS as $header => $values) {
            $headerValues = $response->headers->all($header);

            $this->assertNotNull($headerValues);
            $this->assertIsArray($headerValues);
            $this->assertNotEmpty($headerValues);
            $this->assertEquals($values, $headerValues);

            $this->assertIsArray($response->headers->all($header));
        }
    }

    public function testResponseContent(): void
    {
        $response = ResponseTool::getResponse();

        $content = $response->getContent();

        $this->assertNotNull($content);
    }

    public function testResponseContentJson(): void
    {
        $response = ResponseTool::getResponse(new NotAuthorizeModel(), Response::HTTP_UNAUTHORIZED);

        $content = $response->getContent();

        $this->assertNotNull($content);
        $this->assertJson($content);
    }
}
