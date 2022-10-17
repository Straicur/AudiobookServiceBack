<?php

namespace App\Tests\Tool;

use App\Model\NotAuthorizeModel;
use App\Tool\ResponseTool;
use PHPUnit\Framework\TestCase;

class ResponseToolTest extends TestCase
{
    private static array $_HEADERS = [
        "Content-Type" => [
            "application/json"
        ]
    ];

    public function testResponseHeaders()
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

    public function testResponseContent()
    {
        $response = ResponseTool::getResponse();

        $content = $response->getContent();

        $this->assertNotNull($content);
    }

    public function testResponseContentJson()
    {
        $response = ResponseTool::getResponse(new NotAuthorizeModel(), 401);

        $content = $response->getContent();

        $this->assertNotNull($content);
        $this->assertJson($content);
    }
}
