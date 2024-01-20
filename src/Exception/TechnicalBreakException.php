<?php

namespace App\Exception;

use App\Model\Error\TechnicalBreakExceptionModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;

/**
 * TechnicalBreakException
 */
class TechnicalBreakException extends \Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new TechnicalBreakExceptionModel(), 503);
    }
}