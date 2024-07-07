<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidJsonDataException;
use App\Serializer\JsonSerializer;
use App\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class RequestService implements RequestServiceInterface
{
    private readonly SerializerInterface $serializer;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslateService $translateService
    ) {
        $this->serializer = new JsonSerializer();
    }

    public function getRequestBodyContent(Request $request, string $className): object
    {
        $bodyContent = $request->getContent();

        try {
            $query = $this->serializer->deserialize($bodyContent, $className);
        } catch (Throwable $e) {
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
        }

        $this->translateService->setPreferredLanguage($request);
        throw new InvalidJsonDataException($this->translateService);
    }
}
