<?php

namespace App\Tool;

use App\Model\ModelInterface;
use App\Serializer\JsonSerializer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * ResponseTool
 *
 */
class ResponseTool
{
    private static array $headers = [
        "Content-Type" => "application/json"
    ];

    public static function getResponse(?ModelInterface $responseModel = null, int $httpCode = 200): Response{
        $serializeService = new JsonSerializer();

        $serializedObject = $responseModel != null ? $serializeService->serialize($responseModel) : null;

        return new Response($serializedObject, $httpCode, self::$headers);
    }

    public static function getBinaryFileResponse($fileDir, int $httpCode = 200): Response{

        $response = new BinaryFileResponse($fileDir);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($fileDir)
        );

        $response->deleteFileAfterSend();

        return $response;
    }
}