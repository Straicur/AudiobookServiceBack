<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminTechnicalBreakPatchQuery
{
    #[Assert\NotNull(message: 'ReportId is null')]
    #[Assert\NotBlank(message: 'ReportId is blank')]
    #[Assert\Uuid]
    private Uuid $technicalBreakId;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getTechnicalBreakId(): Uuid
    {
        return $this->technicalBreakId;
    }

    public function setTechnicalBreakId(string $technicalBreakId): void
    {
        $this->technicalBreakId = Uuid::fromString($technicalBreakId);
    }
}
