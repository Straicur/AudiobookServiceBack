<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminTechnicalBreakListQuery
{
    #[Assert\NotNull(message: "ReportId is null")]
    #[Assert\NotBlank(message: "ReportId is blank")]
    #[Assert\Uuid]
    private Uuid $reportId;
}