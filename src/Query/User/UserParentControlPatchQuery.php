<?php

declare(strict_types = 1);

namespace App\Query\User;

use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class UserParentControlPatchQuery
{
    #[Assert\NotNull(message: 'SmsCode is null')]
    #[Assert\NotBlank(message: 'SmsCode is empty')]
    #[Assert\Type(type: 'string')]
    private string $smsCode;

    #[Assert\Collection(
        fields: [
            'birthday'=> new Assert\NotBlank(allowNull: true)
        ],
        allowMissingFields: true,
    )]
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'birthday', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('birthday', $additionalData)) {
            $additionalData['birthday'] = DateTime::createFromFormat('d.m.Y', $additionalData['birthday']);
        }

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getSmsCode(): string
    {
        return $this->smsCode;
    }

    public function setSmsCode(string $smsCode): void
    {
        $this->smsCode = $smsCode;
    }
}
