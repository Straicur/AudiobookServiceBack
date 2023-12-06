<?php

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserAudiobookCommentEditQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "CategoryKey is null")]
    #[Assert\NotBlank(message: "CategoryKey is empty")]
    #[Assert\Type(type: "string")]
    private string $categoryKey;

    #[Assert\NotNull(message: "AudiobookCommentId is null")]
    #[Assert\NotBlank(message: "AudiobookCommentId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookCommentId;

    #[Assert\NotNull(message: "Comment is null")]
    #[Assert\NotBlank(message: "Comment is empty")]
    #[Assert\Type(type: "string")]
    private string $comment;

    #[Assert\NotNull(message: "Deleted is null")]
    #[Assert\Type(type: "boolean")]
    private bool $deleted;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'parentId' => new Assert\Optional([
                    new Assert\NotBlank(message: "ParentId is empty"),
                    new Assert\NotNull(),
                    new Assert\Uuid()
                ])
            ]
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "parentId", type: "string", example: "UUID", nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists("parentId", $additionalData)) {
            $additionalData["parentId"] = Uuid::fromString($additionalData["parentId"]);
        }

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
    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    /**
     * @param string $categoryKey
     */
    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getAudiobookCommentId(): Uuid
    {
        return $this->audiobookCommentId;
    }

    /**
     * @param string $audiobookCommentId
     */
    public function setAudiobookCommentId(string $audiobookCommentId): void
    {
        $this->audiobookCommentId = Uuid::fromString($audiobookCommentId);;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

}