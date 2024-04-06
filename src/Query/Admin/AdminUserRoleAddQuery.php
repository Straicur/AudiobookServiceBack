<?php

namespace App\Query\Admin;

use App\Enums\UserRoles;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminUserRoleAddQuery
{
    #[Assert\NotNull(message: 'UserId is null')]
    #[Assert\NotBlank(message: 'UserId is blank')]
    #[Assert\Uuid]
    private Uuid $userId;


    #[Assert\NotNull(message: 'Role is null')]
    #[Assert\NotBlank(message: 'Role is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min: 1,
        max: 3,
    )]
    private int $role;

    /**
     * @return Uuid
     */
    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = Uuid::fromString($userId);;
    }

    /**
     * @return UserRoles
     */
    public function getRole(): UserRoles
    {
        return match ($this->role) {
            1 => UserRoles::GUEST,
            2 => UserRoles::USER,
            3 => UserRoles::ADMINISTRATOR,
        };
    }

    /**
     * @param int $role
     */
    public function setRole(int $role): void
    {
        $this->role = $role;
    }

}