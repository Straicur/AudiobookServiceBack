<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class AdminAudiobookAddQuery implements AdminAudiobookAddFileInterface
{
    #[Assert\NotNull(message: 'HashName is null')]
    #[Assert\NotBlank(message: 'HashName is empty')]
    #[Assert\Type(type: 'string')]
    private string $hashName;

    #[Assert\NotNull(message: 'FileName is null')]
    #[Assert\NotBlank(message: 'FileName is empty')]
    #[Assert\Type(type: 'string')]
    private string $fileName;

    #[Assert\NotNull(message: 'Base64 is null')]
    #[Assert\NotBlank(message: 'Base64 is empty')]
    #[Assert\Type(type: 'string')]
    private string $base64;

    #[Assert\NotNull(message: 'Part is null')]
    #[Assert\NotBlank(message: 'Part is empty')]
    #[Assert\Type(type: 'integer')]
    private int $part;

    #[Assert\NotNull(message: 'Parts is null')]
    #[Assert\NotBlank(message: 'Parts is empty')]
    #[Assert\Type(type: 'integer')]
    private int $parts;

    #[Assert\Collection(
        fields: [
            'categories'=> new Assert\All([
                new Assert\NotBlank()
            ]),
            'author'=> new Assert\NotBlank(allowNull: true),
            'title'=> new Assert\NotBlank(allowNull: true),
            'age'=> new Assert\NotBlank(allowNull: true),
            'year'=> new Assert\NotBlank(allowNull: true)
        ],
        allowMissingFields: true,
    )]
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'categories', type: 'array', nullable: true, attachables: [
            new OA\Items(type: 'string', example: 'UUID'),
        ]),
        new OA\Property(property: 'title', type: 'string', example: 'Tytuł', nullable: true),
        new OA\Property(property: 'author', type: 'string', example: 'Autor', nullable: true),
        new OA\Property(property: 'year', type: 'datetime', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'age', type: 'integer', example: 1, nullable: true),
    ], type: 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getHashName(): string
    {
        return $this->hashName;
    }

    public function setHashName(string $hashName): void
    {
        $this->hashName = $hashName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = preg_replace('/\s+/', '_', $fileName);
    }

    public function getBase64(): string
    {
        return $this->base64;
    }

    public function setBase64(string $base64): void
    {
        $this->base64 = $base64;
    }

    public function getPart(): int
    {
        return $this->part;
    }

    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }
}
