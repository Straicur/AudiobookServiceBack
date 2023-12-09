<?php

namespace App\Query\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AudiobookCoversQuery
{
    private array $audiobooks = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('audiobooks', new Assert\All(
            new Assert\Required([
                new Assert\NotBlank(),
                new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: "Bad Uuid"),
                new Assert\Uuid()
            ])
        ));
    }

    public function getAudiobooks(): array
    {


        return $this->audiobooks;
    }

    #[OA\Property(property: "audiobooks", type: "array", nullable: true, attachables: [
        new OA\Items(type: "string", example: "UUID"),
    ])]
    public function setAudiobooks(array $audiobooks): void
    {
        foreach ($audiobooks as &$audiobook) {
            if (Uuid::isValid($audiobook)) {
                $audiobook = Uuid::fromString($audiobook);
            }
        }

        $this->audiobooks = $audiobooks;
    }
}
