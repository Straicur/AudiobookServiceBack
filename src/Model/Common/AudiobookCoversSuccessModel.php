<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Model\ModelInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class AudiobookCoversSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCoverModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AudiobookCoverModel::class))
    )]
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
