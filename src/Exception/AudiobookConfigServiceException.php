<?php

namespace App\Exception;

use App\Model\Error\AudiobookConfigServiceModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;

class AudiobookConfigServiceException extends \Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new AudiobookConfigServiceModel(), 500);
    }
}