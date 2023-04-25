<?php

namespace App\Query;

use App\Enums\NotificationOrderSearch;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminUserNotificationsQuery
{
    #[Assert\NotNull(message: "Page is null")]
    #[Assert\NotBlank(message: "Page is empty")]
    #[Assert\Type(type: "integer")]
    private int $page;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "integer")]
    private int $limit;
    protected array $searchData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('searchData', new Assert\Collection([
            'fields' => [
                'text' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Text is empty'),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'type' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Type is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(7)
                ]),
                'deleted' => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'order' => new Assert\Optional([
                    new Assert\NotBlank(message: 'Order is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(9)
                ])
            ],
        ]));
    }

    /**
     * @param string[] $searchData
     */
    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'text', type: 'string', example: 'text', nullable: true),
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'deleted', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
    ], type: 'object')]
    public function setSearchData(array $searchData): void
    {
        if (array_key_exists('order', $searchData)) {
            if ($searchData["order"] != NotificationOrderSearch::LATEST->value && $searchData["order"] != NotificationOrderSearch::OLDEST->value) {
                $searchData["order"] = NotificationOrderSearch::LATEST->value;
            }
        }

        $this->searchData = $searchData;
    }

    /**
     * @return string[]
     */
    public function getSearchData(): array
    {
        return $this->searchData;
    }

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 0)]
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 10)]
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

}