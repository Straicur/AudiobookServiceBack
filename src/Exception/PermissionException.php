<?php

namespace App\Exception;

use App\Model\Error\PermissionNotGrantedModel;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionException
 */
class PermissionException extends \Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new PermissionNotGrantedModel(), 403);
    }
}