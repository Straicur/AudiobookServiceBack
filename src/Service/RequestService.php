<?php

namespace App\Service;

use App\Exception\InvalidJsonDataException;
use App\Serializer\JsonSerializer;
use App\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestService implements RequestServiceInterface
{
    private readonly ValidatorInterface $validator;

    private readonly SerializerInterface $serializer;
    private TranslateService $translateService;

    public function __construct(ValidatorInterface $validator, TranslateService $translateService)
    {
        $this->validator = $validator;
        $this->translateService = $translateService;
        $this->serializer = new JsonSerializer();
    }

    /**
     * @throws InvalidJsonDataException
     */
    public function getRequestBodyContent(Request $request, string $className): object
    {
        $bodyContent = $request->getContent();

        try {
            $query = $this->serializer->deserialize($bodyContent, $className);
        } catch (\Exception $e) {
            $this->translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($this->translateService, null, [$e->getMessage()]);
        }

        if ($query instanceof $className) {
            $validationErrors = $this->validator->validate($query);
            if ($validationErrors->count() > 0) {
                $this->translateService->setPreferredLanguage($request);
                throw new InvalidJsonDataException($this->translateService, $validationErrors);
            }

            return $query;
        } else {
            $this->translateService->setPreferredLanguage($request);
            throw new InvalidJsonDataException($this->translateService);
        }
    }
}