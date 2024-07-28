<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\UserDeletedExceptionModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserDeletedException extends Exception implements ResponseExceptionInterface
{
    private array $dataStrings;

    public function __construct(array $dataStrings = [])
    {
        parent::__construct('User is Delted');

        $this->dataStrings = $dataStrings;
    }

    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new UserDeletedExceptionModel($this->dataStrings), 409);
    }
}
