<?php

declare(strict_types=1);

namespace App\Exception;

use App\Model\Error\JsonDataInvalidModel;
use App\Service\TranslateService;
use App\Tool\ResponseTool;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidJsonDataException extends \Exception implements ResponseExceptionInterface
{
    protected $message;

    private ?ConstraintViolationListInterface $validationErrors;

    private ?array $errors;

    /**
     * @param TranslateService $translateService
     * @param ConstraintViolationListInterface|null $validationErrors
     * @param string[]|null $errors
     */
    public function __construct(TranslateService $translateService, ?ConstraintViolationListInterface $validationErrors = null, ?array $errors = null)
    {
        parent::__construct('Bad request');

        $this->message = $translateService->getTranslation('InvalidJson');
        $this->validationErrors = $validationErrors;
        $this->errors = $errors;
    }

    public function getResponse(): Response
    {
        $validationErrors = [];

        for ($i = 0; $i < $this->validationErrors?->count(); $i++) {
            $validationError = $this->validationErrors->get($i);

            $validationErrors[] = '[' . $validationError->getPropertyPath() . '] -> ' . $validationError->getMessage();
        }

        if ($this->errors !== null) {
            foreach ($this->errors as $error) {
                $validationErrors[] = $error;
            }
        }

        return ResponseTool::getResponse(new JsonDataInvalidModel($this->message, $validationErrors), 400);
    }
}