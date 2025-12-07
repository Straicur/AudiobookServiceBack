<?php

declare(strict_types = 1);

namespace App\Exception;

use App\Model\Error\JsonDataInvalidModel;
use App\Service\TranslateServiceInterface;
use App\Tool\ResponseTool;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidJsonDataException extends Exception implements ResponseExceptionInterface
{
    protected $message;

    /**
     * @param string[]|null $errors
     */
    public function __construct(TranslateServiceInterface $translateService, private readonly ?ConstraintViolationListInterface $validationErrors = null, private readonly ?array $errors = null)
    {
        parent::__construct('Bad request');

        $this->message = $translateService->getTranslation('InvalidJson');
    }

    public function getResponse(): Response
    {
        $validationErrors = [];

        for ($i = 0; $this->validationErrors?->count() > $i; ++$i) {
            $validationError = $this->validationErrors->get($i);

            $validationErrors[] = '[' . $validationError->getPropertyPath() . '] -> ' . $validationError->getMessage();
        }

        if (null !== $this->errors) {
            foreach ($this->errors as $error) {
                $validationErrors[] = $error;
            }
        }

        return ResponseTool::getResponse(new JsonDataInvalidModel($this->message, $validationErrors), Response::HTTP_BAD_REQUEST);
    }
}
