<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminCategoryAddQuery
{
    #[Assert\NotNull(message: "Name is null")]
    #[Assert\NotBlank(message: "Name is empty")]
    #[Assert\Type(type: "string")]
    private string $name;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

}