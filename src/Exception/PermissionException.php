<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\PermissionNotGrantedModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class PermissionException extends Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new PermissionNotGrantedModel(), 403);
    }
}