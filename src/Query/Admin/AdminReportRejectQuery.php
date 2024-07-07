<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminReportRejectQuery
{
    #[Assert\NotNull(message: 'ReportId is null')]
    #[Assert\NotBlank(message: 'ReportId is blank')]
    #[Assert\Uuid]
    private Uuid $reportId;

    #[Assert\NotNull(message: 'Response is null')]
    #[Assert\NotBlank(message: 'Response is empty')]
    #[Assert\Type(type: 'string')]
    private string $response;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getReportId(): Uuid
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): void
    {
        $this->reportId = Uuid::fromString($reportId);
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

}