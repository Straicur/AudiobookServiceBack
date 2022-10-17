<?php

namespace App\Tool;

use App\Model\ModelInterface;
use App\Serializer\JsonSerializer;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResponseTool
 *
 */
class ResponseTool
{
    private static array $headers = [
        "Content-Type" => "application/json"
    ];

    private static array $pdfHeaders = [
        "Content-Type" => "application/pdf",
        'Content-Disposition'=> 'attachment; filename=document.pdf'
    ];

    public static function getResponse(?ModelInterface $responseModel = null, int $httpCode = 200): Response{
        $serializeService = new JsonSerializer();

        $serializedObject = $responseModel != null ? $serializeService->serialize($responseModel) : null;

        return new Response($serializedObject, $httpCode, self::$headers);
    }

    public static function getPDFResponse($blob, int $httpCode = 200): Response{

        return new Response(stream_get_contents($blob), $httpCode, self::$pdfHeaders);

    }
}