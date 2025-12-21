<?php

declare(strict_types = 1);

namespace App\Query\User;

use App\Enums\ReportType;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UserNotAuthorizedUserReportQuery
{
    #[Assert\NotNull(message: 'Type is null')]
    #[Assert\NotBlank(message: 'Type is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\GreaterThan(0)]
    #[Assert\LessThan(9)]
    private int $type;

    #[Assert\NotNull(message: 'Ip is null')]
    #[Assert\Type(type: 'string')]
    private string $ip;

    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email]
    private string $email;

    #[Assert\Collection(
        fields: [
            'description'=> new Assert\NotBlank(allowNull: true),
            'actionId'=> new Assert\NotBlank(allowNull: true)
        ],
        allowMissingFields: true,
    )]
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'description', type: 'string', example: 'Desc', nullable: true),
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getType(): ReportType
    {
        return match ($this->type) {
            2       => ReportType::AUDIOBOOK_PROBLEM,
            3       => ReportType::CATEGORY_PROBLEM,
            4       => ReportType::SYSTEM_PROBLEM,
            5       => ReportType::USER_PROBLEM,
            6       => ReportType::SETTINGS_PROBLEM,
            7       => ReportType::RECRUITMENT_REQUEST,
            default => ReportType::OTHER,
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
