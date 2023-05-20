<?php

namespace App\Model;

class AudiobookCommentsModel
{
    private AudiobookCommentUserModel $userModel;
    private string $id;
    private string $comment;
    private bool $edited;
    private bool $myComment;
    private ?bool $deleted = null;
    private ?bool $liked = null;
    private int $audiobookCommentLike = 0;
    private int $audiobookCommentUnlike = 0;

    private ?string $parentId = null;

    /**
     * @var AudiobookCommentsModel[]
     */
    private array $children = [];

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
     * @return AudiobookCommentsModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(AudiobookCommentsModel $children): void
    {
        $this->children[] = $children;
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
     * @return int
     */
    public function getAudiobookCommentLike(): int
    {
        return $this->audiobookCommentLike;
    }

    /**
     * @param int $audiobookCommentLike
     */
    public function setAudiobookCommentLike(int $audiobookCommentLike): void
    {
        $this->audiobookCommentLike = $audiobookCommentLike;
    }

    /**
     * @return int
     */
    public function getAudiobookCommentUnlike(): int
    {
        return $this->audiobookCommentUnlike;
    }

    /**
     * @param int $audiobookCommentUnlike
     */
    public function setAudiobookCommentUnlike(int $audiobookCommentUnlike): void
    {
        $this->audiobookCommentUnlike = $audiobookCommentUnlike;
    }

    /**
     * @return bool|null
     */
    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(?bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @return bool|null
     */
    public function getLiked(): ?bool
    {
        return $this->liked;
    }

    /**
     * @param bool|null $liked
     */
    public function setLiked(?bool $liked): void
    {
        $this->liked = $liked;
    }

    /**
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @param string|null $parentId
     */
    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

}