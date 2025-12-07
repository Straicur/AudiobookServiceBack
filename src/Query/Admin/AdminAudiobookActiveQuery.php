<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\UserAudiobookActivationType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminAudiobookActiveQuery
{
    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is empty')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: 'Active is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $active;

    /**
     * @Assert\Collection(fields={})
     */
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'text', type: 'string', example: 'desc', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (
            array_key_exists('type', $additionalData)
            && $additionalData['type'] !== UserAudiobookActivationType::ALL->value
            && $additionalData['type'] !== UserAudiobookActivationType::CATEGORY_PROPOSED_RELATED->value
            && $additionalData['type'] !== UserAudiobookActivationType::MY_LIST_RELATED->value
            && $additionalData['type'] !== UserAudiobookActivationType::AUDIOBOOK_INFO_RELATED->value
        ) {
            $additionalData['type'] = UserAudiobookActivationType::ALL->value;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
