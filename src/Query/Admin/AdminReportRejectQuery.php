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

    #[Assert\NotNull(message: 'RejectOthers is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $rejectOthers;

    #[Assert\NotNull(message: 'Answer is null')]
    #[Assert\NotBlank(message: 'Answer is empty')]
    #[Assert\Type(type: 'string')]
    private ?string $answer = null;

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getReportId(): Uuid
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): void
    {
        $this->reportId = Uuid::fromString($reportId);
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function isRejectOthers(): bool
    {
        return $this->rejectOthers;
    }

    public function setRejectOthers(bool $rejectOthers): void
    {
        $this->rejectOthers = $rejectOthers;
    }
}
