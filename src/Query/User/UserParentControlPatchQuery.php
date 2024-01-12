<?php

namespace App\Query\User;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserParentControlPatchQuery
{
    #[Assert\NotNull(message: "SmsCode is null")]
    #[Assert\NotBlank(message: "SmsCode is empty")]
    #[Assert\Type(type: "string")]
    private string $smsCode;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'birthday' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Birthday is empty'),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
            ]
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: 'birthday', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('birthday', $additionalData)) {
            $additionalData['birthday'] = \DateTime::createFromFormat('d.m.Y', $additionalData['birthday']);
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

    public function getSmsCode(): string
    {
        return $this->smsCode;
    }

    public function setSmsCode(string $smsCode): void
    {
        $this->smsCode = $smsCode;
    }

}