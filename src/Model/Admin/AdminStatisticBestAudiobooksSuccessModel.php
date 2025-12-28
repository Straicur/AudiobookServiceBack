<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminStatisticBestAudiobooksSuccessModel implements ModelInterface
{
    public function __construct(private ?AdminAudiobookDetailsModel $firstAudiobook = null, private ?AdminAudiobookDetailsModel $secondAudiobook = null, private ?AdminAudiobookDetailsModel $thirdAudiobook = null) {}

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
