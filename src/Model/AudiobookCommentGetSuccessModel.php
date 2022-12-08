<?php

namespace App\Model;

class AudiobookCommentGetSuccessModel implements ModelInterface
{
    /**
     * @var AudiobookCommentGetModel[]
     */
    private array $audiobookCommentGetModels;

    /**
     * @return AudiobookCommentGetModel[]
     */
    public function getAudiobookCommentGetModels(): array
    {
        return $this->audiobookCommentGetModels;
    }

    /**
     * @param AudiobookCommentGetModel[] $audiobookCommentGetModels
     */
    public function setAudiobookCommentGetModels(array $audiobookCommentGetModels): void
    {
        $this->audiobookCommentGetModels = $audiobookCommentGetModels;
    }

    public function addAudiobookCommentGetModel(AudiobookCommentGetModel $audiobookCommentGetModel): void
    {
        $this->audiobookCommentGetModels[] = $audiobookCommentGetModel;
    }
}