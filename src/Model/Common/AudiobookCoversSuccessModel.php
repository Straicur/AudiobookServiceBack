<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookCoversSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCoverModel[]
     */
    private array $audiobookCoversModels;

    /**
     * @return AudiobookCoverModel[]
     */
    public function getAudiobookCoversModels(): array
    {
        return $this->audiobookCoversModels;
    }

    /**
     * @param AudiobookCoverModel[] $audiobookCoversModels
     */
    public function setAudiobookCoversModels(array $audiobookCoversModels): void
    {
        $this->audiobookCoversModels = $audiobookCoversModels;
    }

    public function addAudiobookCoversModel(AudiobookCoverModel $audiobookCoversModel): void
    {
        $this->audiobookCoversModels[] = $audiobookCoversModel;
    }
}
