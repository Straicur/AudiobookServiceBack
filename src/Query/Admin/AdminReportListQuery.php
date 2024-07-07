<?php

namespace App\Query\Admin;

use App\Enums\ReportOrderSearch;
use App\Enums\ReportType;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminReportListQuery
{
    #[Assert\NotNull(message: 'Page is null')]
    #[Assert\NotBlank(message: 'Page is empty')]
    #[Assert\Type(type: 'integer')]
    private int $page;

    #[Assert\NotNull(message: 'Limit is null')]
    #[Assert\NotBlank(message: 'Limit is empty')]
    #[Assert\Type(type: 'integer')]
    private int $limit;

    protected array $searchData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('searchData', new Assert\Collection([
            'fields' => [
                'actionId'    => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', message: 'Bad Uuid'),
                    new Assert\Uuid(),
                ]),
                'description' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'email'       => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'ip'          => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'string', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'type'        => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(7),
                ]),
                'user'        => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'accepted'    => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'denied'      => new Assert\Optional([
                    new Assert\Type(type: 'boolean', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'dateFrom'    => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'dateTo'      => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'datetime', message: 'The value {{ value }} is not a valid {{ type }}'),
                ]),
                'order'       => new Assert\Optional([
                    new Assert\NotBlank(message: 'Order is empty'),
                    new Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}'),
                    new Assert\GreaterThan(0),
                    new Assert\LessThan(3),
                ]),
            ],
        ]));
    }

    #[OA\Property(property: 'searchData', properties: [
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'description', type: 'string', example: 'description', nullable: true),
        new OA\Property(property: 'email', type: 'string', example: 'fdas@gmail.com', nullable: true),
        new OA\Property(property: 'ip', type: 'string', example: '192.021.32', nullable: true),
        new OA\Property(property: 'type', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'user', type: 'string', example: true, nullable: true),
        new OA\Property(property: 'accepted', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'denied', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'dateFrom', type: 'datetime', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'dateTo', type: 'datetime', example: 'd.m.Y', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1, nullable: true),
    ], type    : 'object')]
    public function setSearchData(array $searchData): void
    {
        if (array_key_exists('actionId', $searchData) && Uuid::isValid($searchData['actionId'])) {
            $searchData['actionId'] = Uuid::fromString($searchData['actionId']);
        }

        if (array_key_exists('type', $searchData) && $searchData['type'] !== ReportType::COMMENT->value && $searchData['type'] !== ReportType::AUDIOBOOK_PROBLEM->value && $searchData['type'] !== ReportType::CATEGORY_PROBLEM->value && $searchData['type'] !== ReportType::SYSTEM_PROBLEM->value && $searchData['type'] !== ReportType::USER_PROBLEM->value && $searchData['type'] !== ReportType::USER_PROBLEM->value) {
            $searchData['type'] = ReportType::COMMENT->value;
        }

        if (array_key_exists('order', $searchData) && $searchData['order'] !== ReportOrderSearch::OLDEST->value && $searchData['order'] !== ReportOrderSearch::LATEST->value) {
            $searchData['order'] = ReportType::COMMENT->value;
        }

        if (array_key_exists('dateFrom', $searchData)) {
            $searchData['dateFrom'] = DateTime::createFromFormat('d.m.Y', $searchData['dateFrom']);
        }

        if (array_key_exists('dateTo', $searchData)) {
            $searchData['dateTo'] = DateTime::createFromFormat('d.m.Y', $searchData['dateTo']);
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
