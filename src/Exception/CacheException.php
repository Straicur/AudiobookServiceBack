<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\CacheExceptionModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class CacheException extends Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new CacheExceptionModel(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
