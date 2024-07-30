<?php

namespace App\Query\User;

use App\Enums\ReportType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserNotAuthorizedUserReportQuery
{
    #[Assert\NotNull(message: 'Type is null')]
    #[Assert\NotBlank(message: 'Type is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\GreaterThan(0)]
    #[Assert\LessThan(8)]
    private int $type;

    #[Assert\NotNull(message: 'Ip is null')]
    #[Assert\Type(type: 'string')]
    private string $ip;

    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email]
    private string $email;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'description' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Description is empty'),
                    new Assert\Type(type: 'string'),
                ]),
                'actionId'    => new Assert\Optional([
                    new Assert\NotBlank(message: 'ActionId is empty'),
                    new Assert\Uuid(),
                ]),
            ],
        ]));
    }


    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'description', type: 'string', example: 'Desc', nullable: true),
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('actionId', $additionalData) && Uuid::isValid($additionalData['actionId'])) {
            $additionalData['actionId'] = Uuid::fromString($additionalData['actionId']);
        }

        $this->additionalData = $additionalData;
    }


    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getType(): ReportType
    {
        return match ($this->type) {
            2 => ReportType::AUDIOBOOK_PROBLEM,
            3 => ReportType::CATEGORY_PROBLEM,
            4 => ReportType::SYSTEM_PROBLEM,
            5 => ReportType::USER_PROBLEM,
            6 => ReportType::SETTINGS_PROBLEM,
            7 => ReportType::RECRUITMENT_REQUEST,
            default => ReportType::COMMENT,
        };
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
