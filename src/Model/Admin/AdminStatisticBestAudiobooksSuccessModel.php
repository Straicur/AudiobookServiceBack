<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminStatisticBestAudiobooksSuccessModel implements ModelInterface
{
    private ?AdminAudiobookDetailsModel $firstAudiobook;
    private ?AdminAudiobookDetailsModel $secondAudiobook;
    private ?AdminAudiobookDetailsModel $thirdAudiobook;

    public function __construct(?AdminAudiobookDetailsModel $firstAudiobook = null, ?AdminAudiobookDetailsModel $secondAudiobook = null, ?AdminAudiobookDetailsModel $thirdAudiobook = null)
    {
        $this->firstAudiobook = $firstAudiobook;
        $this->secondAudiobook = $secondAudiobook;
        $this->thirdAudiobook = $thirdAudiobook;
    }

    public function getFirstAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->firstAudiobook;
    }

    public function setFirstAudiobook(AdminAudiobookDetailsModel $firstAudiobook): void
    {
        $this->firstAudiobook = $firstAudiobook;
    }

    public function getSecondAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->secondAudiobook;
    }

    public function setSecondAudiobook(AdminAudiobookDetailsModel $secondAudiobook): void
    {
        $this->secondAudiobook = $secondAudiobook;
    }

    public function getThirdAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->thirdAudiobook;
    }

    public function setThirdAudiobook(AdminAudiobookDetailsModel $thirdAudiobook): void
    {
        $this->thirdAudiobook = $thirdAudiobook;
    }
}
