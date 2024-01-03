<?php

namespace App\Query\Admin;

use App\Enums\UserAudiobookActivationType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminAudiobookActiveQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is empty")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "Active is null")]
    #[Assert\Type(type: "boolean")]
    private bool $active;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'type' => new Assert\Optional([
                    new Assert\NotBlank(message: "Type is empty"),
                    new Assert\NotNull(),
                    new Assert\Type("integer"),
                    new Assert\GreaterThanOrEqual(1),
                    new Assert\LessThanOrEqual(4)
                ]),
                'text' => new Assert\Optional([
                    new Assert\NotBlank(message: "Text is empty"),
                    new Assert\NotNull(),
                    new Assert\Type("string")
                ])
            ]
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "type", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "text", type: "string", example: "desc", nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists("type", $additionalData) && $additionalData["type"] !== UserAudiobookActivationType::ALL->value && $additionalData["type"] !== UserAudiobookActivationType::CATEGORY_PROPOSED_RELATED->value && $additionalData["type"] !== UserAudiobookActivationType::MY_LIST_RELATED->value && $additionalData["type"] !== UserAudiobookActivationType::AUDIOBOOK_INFO_RELATED->value) {
            $additionalData["type"] = UserAudiobookActivationType::ALL->value;
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
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

}