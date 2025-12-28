<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminAudiobookChangeCoverQuery
{
    #[Assert\NotNull(message: 'Type is null')]
    #[Assert\NotBlank(message: 'Type is empty')]
    #[Assert\Type(type: 'string')]
    private string $type;

    #[Assert\NotNull(message: 'Base64 is null')]
    #[Assert\NotBlank(message: 'Base64 is empty')]
    #[Assert\Type(type: 'string')]
    private string $base64;

    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is blank')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);
    }

    public function getBase64(): string
    {
        return $this->base64;
    }

    public function setBase64(string $base64): void
    {
        $this->base64 = $base64;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
