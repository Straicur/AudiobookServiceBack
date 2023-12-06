<?php

namespace App\Query\Admin;

use App\Enums\ReportType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminReportListQuery
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
                'actionId' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: 'Bad Uuid'),
                    new Assert\Uuid(),
                ]),
                'description' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'type' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(7)
                ]),
            ],
        ]));
    }

    /**
     * @param string[] $searchData
     */
    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'description', type: 'string', example: 'description', nullable: true),
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
    ], type: 'object')]
    public function setSearchData(array $searchData): void
    {
        if(array_key_exists('actionId', $searchData) && Uuid::isValid($searchData["actionId"])){
            $searchData["actionId"] = Uuid::fromString($searchData["actionId"]);
        }
        if(array_key_exists('type', $searchData)){
            $searchData["type"] =  match ((int) $searchData["type"]) {
                1 => ReportType::COMMENT,
                2 => ReportType::AUDIOBOOK_PROBLEM,
                3 => ReportType::CATEGORY_PROBLEM,
                4 => ReportType::SYSTEM_PROBLEM,
                5 => ReportType::USER_PROBLEM,
                6 => ReportType::SETTINGS_PROBLEM,
                default => ReportType::COMMENT,
            };
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