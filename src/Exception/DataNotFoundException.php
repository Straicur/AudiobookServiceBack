<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\DataNotFoundModel;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class DataNotFoundException extends Exception implements ResponseExceptionInterface
{
    private array $dataStrings;

    /**
     * @param string[] $dataStrings
     */
    public function __construct(array $dataStrings = [])
    {
        parent::__construct('Data not found');

        $this->dataStrings = $dataStrings;
    }

    public function getResponse(): Response
    {
        return ResponseTool::getResponse(new DataNotFoundModel($this->dataStrings), Response::HTTP_NOT_FOUND);
    }
}
