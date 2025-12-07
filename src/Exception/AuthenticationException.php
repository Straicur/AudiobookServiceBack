<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Model\Error\NotAuthorizeModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationException extends Exception implements ResponseExceptionInterface
{
    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new NotAuthorizeModel(), Response::HTTP_UNAUTHORIZED);
    }
}
