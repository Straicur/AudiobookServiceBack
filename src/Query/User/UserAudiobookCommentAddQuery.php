<?php

declare(strict_types = 1);

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class UserAudiobookCommentAddQuery
{
    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is blank')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: 'CategoryKey is null')]
    #[Assert\NotBlank(message: 'CategoryKey is empty')]
    #[Assert\Type(type: 'string')]
    private string $categoryKey;

    #[Assert\NotNull(message: 'Comment is null')]
    #[Assert\NotBlank(message: 'Comment is empty')]
    #[Assert\Type(type: 'string')]
    private string $comment;

    /**
     * @Assert\Collection(fields={})
     */
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'parentId', type: 'string', example: 'UUID', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('parentId', $additionalData)) {
            $additionalData['parentId'] = Uuid::fromString($additionalData['parentId']);
        }

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
