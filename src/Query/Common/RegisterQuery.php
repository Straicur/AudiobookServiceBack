<?php

declare(strict_types = 1);

namespace App\Query\Common;

use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class RegisterQuery
{
    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email(message: "It's not an email")]
    private string $email;

    #[Assert\NotNull(message: 'PhoneNumber is null')]
    #[Assert\NotBlank(message: 'Password is empty')]
    #[Assert\Type(type: 'string')]
    private string $phoneNumber;

    #[Assert\NotNull(message: 'Firstname is null')]
    #[Assert\NotBlank(message: 'Firstname is empty')]
    #[Assert\Type(type: 'string')]
    private string $firstname;

    #[Assert\NotNull(message: 'Lastname is null')]
    #[Assert\NotBlank(message: 'Lastname is empty')]
    #[Assert\Type(type: 'string')]
    private string $lastname;

    #[Assert\NotNull(message: 'Password is null')]
    #[Assert\NotBlank(message: 'Password is empty')]
    #[Assert\Type(type: 'string')]
    private string $password;

    /**
     * @Assert\Collection(fields={})
     */
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'birthday', type: 'datetime', example: 'd.m.Y', nullable: true),
    ], type: 'object')]
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
