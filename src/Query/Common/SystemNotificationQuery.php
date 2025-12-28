<?php

declare(strict_types = 1);

namespace App\Query\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class SystemNotificationQuery
{
    #[Assert\NotNull(message: 'Page is null')]
    #[Assert\NotBlank(message: 'Page is blank')]
    #[Assert\Type('integer')]
    private int $page;

    #[Assert\NotNull(message: 'Limit is null')]
    #[Assert\NotBlank(message: 'Limit is blank')]
    #[Assert\Type('integer')]
    private int $limit;

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
