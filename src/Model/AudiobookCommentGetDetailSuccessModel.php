<?php

namespace App\Model;

class AudiobookCommentGetDetailSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCommentGetDetailModel[]
     */
    private array $audiobookCommentGetDetailModels;

    /**
     * @return AudiobookCommentGetDetailModel[]
     */
    public function getAudiobookCommentGetDetailModels(): array
    {
        return $this->audiobookCommentGetDetailModels;
    }

    /**
     * @param AudiobookCommentGetDetailModel[] $audiobookCommentGetDetailModels
     */
    public function setAudiobookCommentGetDetailModels(array $audiobookCommentGetDetailModels): void
    {
        $this->audiobookCommentGetDetailModels = $audiobookCommentGetDetailModels;
    }

    public function addAudiobookCommentGetDetailModel(AudiobookCommentGetDetailModel $audiobookCommentGetDetailModel): void
    {
        $this->audiobookCommentGetDetailModels[] = $audiobookCommentGetDetailModel;
    }
}