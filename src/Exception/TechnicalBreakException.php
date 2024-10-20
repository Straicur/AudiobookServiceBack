<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\TechnicalBreakExceptionModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TechnicalBreakException extends Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new TechnicalBreakExceptionModel(), Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
