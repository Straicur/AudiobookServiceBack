<?php

namespace App\Query;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;

class AudiobookCoversQuery
{
    private array $audiobooks = [];

    /**
     * @return array
     */
    #[OA\Property(property: "audiobooks", type: "array", nullable: true, attachables: [
        new OA\Items(type: "string", example: "UUID"),
    ])]
    public function getAudiobooks(): array
    {
        foreach ($this->audiobooks as &$audiobook) {
            if (Uuid::isValid($audiobook)) {
                $audiobook = Uuid::fromString($audiobook);
            }
        }

        return $this->audiobooks;
    }

    /**
     * @param array $audiobooks
     */
    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }
}
