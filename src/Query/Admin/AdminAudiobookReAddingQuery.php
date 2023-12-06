<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminAudiobookReAddingQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "HashName is null")]
    #[Assert\NotBlank(message: "HashName is empty")]
    #[Assert\Type(type: "string")]
    private string $hashName;

    #[Assert\NotNull(message: "FileName is null")]
    #[Assert\NotBlank(message: "FileName is empty")]
    #[Assert\Type(type: "string")]
    private string $fileName;

    #[Assert\NotNull(message: "Base64 is null")]
    #[Assert\NotBlank(message: "Base64 is empty")]
    #[Assert\Type(type: "string")]
    private string $base64;

    #[Assert\NotNull(message: "Part is null")]
    #[Assert\NotBlank(message: "Part is empty")]
    #[Assert\Type(type: "integer")]
    private int $part;

    #[Assert\NotNull(message: "Parts is null")]
    #[Assert\NotBlank(message: "Parts is empty")]
    #[Assert\Type(type: "integer")]
    private int $parts;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'categories' => new Assert\Optional([
                    new Assert\NotBlank(message: "Categories is empty"),
                    new Assert\All(constraints: [
                        new Assert\NotBlank(),
                        new Assert\Uuid()
                    ])
                ]),
                'title' => new Assert\Optional([
                    new Assert\NotBlank(message: "Title is empty"),
                    new Assert\Type(type: "string")
                ]),
                'author' => new Assert\Optional([
                    new Assert\NotBlank(message: "Author is empty"),
                    new Assert\Type(type: "string")
                ]),
            ],
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "categories", type: "array", nullable: true, attachables: [
            new OA\Items(type: "string", example: "UUID"),
        ]),
        new OA\Property(property: "title", type: "string", example: "Tytuł", nullable: true),
        new OA\Property(property: "author", type: "string", example: "Autor", nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return string[]
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    /**
     * @param string $audiobookId
     */
    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);;
    }

    /**
     * @return string
     */
    public function getHashName(): string
    {
        return $this->hashName;
    }

    /**
     * @param string $hashName
     */
    public function setHashName(string $hashName): void
    {
        $this->hashName = $hashName;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = preg_replace('/\s+/', '_', $fileName);
    }

    /**
     * @return string
     */
    public function getBase64(): string
    {
        return $this->base64;
    }

    /**
     * @param string $base64
     */
    public function setBase64(string $base64): void
    {
        $this->base64 = $base64;
    }

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    /**
     * @return int
     */
    public function getParts(): int
    {
        return $this->parts;
    }

    /**
     * @param int $parts
     */
    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }
}