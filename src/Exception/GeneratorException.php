<?php

namespace App\Exception;

use App\Model\Error\GeneratorExceptionModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;

class GeneratorException extends \Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new GeneratorExceptionModel(), 500);
    }
}