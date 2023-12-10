<?php

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserNotAuthorizedUserReportQuery
{
    #[Assert\NotNull(message: "Page is null")]
    #[Assert\NotBlank(message: "Page is empty")]
    #[Assert\Type(type: "integer")]
    #[Assert\GreaterThan(0)]
    #[Assert\LessThan(7)]
    private int $type;

    #[Assert\NotNull(message: "Ip is null")]
    #[Assert\NotBlank(message: "Ip is empty")]
    #[Assert\Type(type: "string")]
    private string $ip;


    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'description' => new Assert\Optional([
                    new Assert\NotBlank(message: "Description is empty"),
                    new Assert\Type(type: "string")
                ]),
                'actionId' => new Assert\Optional([
                    new Assert\NotBlank(message: "ActionId is empty"),
                    new Assert\Type(type: "string")
                ])
            ],
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "description", type: "string", example: "Desc", nullable: true),
        new OA\Property(property: "actionId", type: "string", example: "UUID", nullable: true)
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('actionId', $additionalData) && Uuid::isValid($additionalData["actionId"])) {
            $additionalData["actionId"] = Uuid::fromString($additionalData["actionId"]);
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

}