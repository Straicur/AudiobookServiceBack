<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\GeneratorExceptionModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class GeneratorException extends Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new GeneratorExceptionModel(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
