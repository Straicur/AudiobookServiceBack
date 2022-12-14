<?php

namespace App\Model;

class AudiobookCommentGetDetailModel
{
    private AudiobookCommentUserModel $userModel;
    private string $id;
    private string $comment;
    private bool $edited;
    private bool $myComment;

    /**
     * @var AudiobookCommentLikeModel[]
     */
    private array $audiobookCommentLikeModel;

    /**
     * @var AudiobookCommentLikeModel[]
     */
    private array $audiobookCommentUnlikeModel;
    /**
     * @param AudiobookCommentUserModel $userModel
     * @param string $id
     * @param string $comment
     * @param bool $edited
     * @param bool $myComment
     */
    public function __construct(AudiobookCommentUserModel $userModel, string $id, string $comment, bool $edited, bool $myComment)
    {
        $this->userModel = $userModel;
        $this->id = $id;
        $this->comment = $comment;
        $this->edited = $edited;
        $this->myComment = $myComment;
    }

    /**
     * @return AudiobookCommentUserModel
     */
    public function getUserModel(): AudiobookCommentUserModel
    {
        return $this->userModel;
    }

    /**
     * @param AudiobookCommentUserModel $userModel
     */
    public function setUserModel(AudiobookCommentUserModel $userModel): void
    {
        $this->userModel = $userModel;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->edited;
    }

    /**
     * @param bool $edited
     */
    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    /**
     * @return bool
     */
    public function isMyComment(): bool
    {
        return $this->myComment;
    }

    /**
     * @param bool $myComment
     */
    public function setMyComment(bool $myComment): void
    {
        $this->myComment = $myComment;
    }

    /**
     * @return AudiobookCommentLikeModel[]
     */
    public function getAudiobookCommentLikeModel(): array
    {
        return $this->audiobookCommentLikeModel;
    }

    /**
     * @param AudiobookCommentLikeModel[] $audiobookCommentLikeModel
     */
    public function setAudiobookCommentLikeModel(array $audiobookCommentLikeModel): void
    {
        $this->audiobookCommentLikeModel = $audiobookCommentLikeModel;
    }

    public function addAudiobookCommentModel(AudiobookCommentLikeModel $audiobookCommentLikeModel): void
    {
        $this->audiobookCommentLikeModel[] = $audiobookCommentLikeModel;
    }

    /**
     * @return AudiobookCommentLikeModel[]
     */
    public function getAudiobookCommentUnlikeModel(): array
    {
        return $this->audiobookCommentUnlikeModel;
    }

    /**
     * @param AudiobookCommentlikeModel[] $audiobookCommentUnlikeModel
     */
    public function setAudiobookCommentUnlikeModel(array $audiobookCommentUnlikeModel): void
    {
        $this->audiobookCommentUnlikeModel = $audiobookCommentUnlikeModel;
    }

    public function addAudiobookCommentUnlikeModel(AudiobookCommentlikeModel $audiobookCommentUnlikeModel): void
    {
        $this->audiobookCommentUnlikeModel[] = $audiobookCommentUnlikeModel;
    }
}