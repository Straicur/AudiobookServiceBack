<?php

namespace App\Query\Admin;

use App\Enums\BanPeriodRage;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminReportAcceptQuery
{
    #[Assert\NotNull(message: 'ReportId is null')]
    #[Assert\NotBlank(message: 'ReportId is blank')]
    #[Assert\Uuid]
    private Uuid $reportId;

    #[Assert\NotNull(message: 'AcceptOthers is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $acceptOthers;

    #[Assert\Type(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min              : 1,
        max              : 8,
    )]
    private ?int $banPeriod = null;

    #[Assert\Type(type: 'string')]
    private ?string $answer = null;

    public function getBanPeriod(): ?BanPeriodRage
    {
        if ($this->banPeriod !== null) {
            return match ($this->banPeriod) {
                1 => BanPeriodRage::SYSTEM,
                2 => BanPeriodRage::NOT_BANNED,
                3 => BanPeriodRage::HALF_DAY_BAN,
                4 => BanPeriodRage::ONE_DAY_BAN,
                5 => BanPeriodRage::FIVE_DAY_BAN,
                6 => BanPeriodRage::ONE_MONTH_BAN,
                7 => BanPeriodRage::THREE_MONTH_BAN,
                8 => BanPeriodRage::ONE_YEAR_BAN,
            };
        }
        return null;
    }

    public function setBanPeriod(int $banPeriod): void
    {
        $this->banPeriod = $banPeriod;
    }

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

    public function isAcceptOthers(): bool
    {
        return $this->acceptOthers;
    }

    public function setAcceptOthers(bool $acceptOthers): void
    {
        $this->acceptOthers = $acceptOthers;
    }
}
