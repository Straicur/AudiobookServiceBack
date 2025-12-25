<?php

declare(strict_types = 1);

namespace App\Query\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AudiobookCoversQuery
{
    #[Assert\All([
        new Assert\NotBlank()
    ])]
    private array $audiobooks = [];

    public function getAudiobooks(): array
    {
        return $this->audiobooks;
    }

    #[OA\Property(property: 'audiobooks', type: 'array', items: new OA\Items(type: 'string', example: 'UUID'), nullable: true)]
    public function setAudiobooks(array $audiobooks): void
    {
        foreach ($audiobooks as &$audiobook) {
            if (Uuid::isValid($audiobook)) {
                $audiobook = Uuid::fromString($audiobook);
            }
        }

        unset($audiobook);

        $this->audiobooks = $audiobooks;
    }
}
