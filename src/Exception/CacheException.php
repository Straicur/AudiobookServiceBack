<?php

namespace App\Exception;

use App\Model\Error\CacheExceptionModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;

class CacheException extends \Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new CacheExceptionModel(), 500);
    }
}