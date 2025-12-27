<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Model\Error\UserDeletedExceptionModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserDeletedException extends Exception implements ResponseExceptionInterface
{
    public function __construct(private readonly array $dataStrings = [])
    {
        parent::__construct('User is Delted');
    }

    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new UserDeletedExceptionModel($this->dataStrings), Response::HTTP_CONFLICT);
    }
}
