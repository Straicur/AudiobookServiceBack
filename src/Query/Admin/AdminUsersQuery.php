<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\UserOrderSearch;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

class AdminUsersQuery
{
    #[Assert\NotNull(message: 'Page is null')]
    #[Assert\NotBlank(message: 'Page is empty')]
    #[Assert\Type(type: 'integer')]
    private int $page;

    #[Assert\NotNull(message: 'Limit is null')]
    #[Assert\NotBlank(message: 'Limit is empty')]
    #[Assert\Type(type: 'integer')]
    private int $limit;

    #[Assert\Collection(
        fields: [
            'email'       => new Assert\NotBlank(allowNull: true),
            'phoneNumber' => new Assert\NotBlank(allowNull: true),
            'firstname'   => new Assert\NotBlank(allowNull: true),
            'lastname'    => new Assert\NotBlank(allowNull: true),
            'active'      => new Assert\NotNull(),
            'banned'      => new Assert\NotNull(),
            'order'       => new Assert\NotBlank(allowNull: true),
        ],
        allowMissingFields: true,
    )]
    protected array $searchData = [];

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'email', type: 'string', example: 'email', nullable: true),
        new OA\Property(property: 'phoneNumber', type: 'string', example: 'phoneNumber', nullable: true),
        new OA\Property(property: 'firstname', type: 'string', example: 'firstname', nullable: true),
        new OA\Property(property: 'lastname', type: 'string', example: 'lastname', nullable: true),
        new OA\Property(property: 'active', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'banned', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (
            array_key_exists('order', $searchData)
            && $searchData['order'] !== UserOrderSearch::LATEST->value
            && $searchData['order'] !== UserOrderSearch::OLDEST->value
            && $searchData['order'] !== UserOrderSearch::ALPHABETICAL_ASC->value
            && $searchData['order'] !== UserOrderSearch::ALPHABETICAL_DESC->value
        ) {
            $searchData['order'] = UserOrderSearch::LATEST->value;
        }

        $this->searchData = $searchData;
    }

    public function getSearchData(): array
    {
        return $this->searchData;
    }

    #[OA\Property(type: 'integer', example: 0)]
    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    #[OA\Property(type: 'integer', example: 10)]
    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
