<?php

declare(strict_types = 1);

namespace App\Model\Common;

class AudiobookCommentsModel
{
    private ?bool $deleted = null;

    private ?bool $liked = null;

    private int $audiobookCommentLike = 0;

    private int $audiobookCommentUnlike = 0;

    private ?string $parentId = null;

    /**
     * @var AudiobookCommentsModel[]
     */
    private array $children = [];

    public function __construct(private AudiobookCommentModel $userModel, private string $id, private string $comment, private bool $edited, private bool $myComment) {}

    /**
     * @return AudiobookCommentsModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(AudiobookCommentsModel $children): void
    {
        $this->children[] = $children;
    }

    public function getUserModel(): AudiobookCommentModel
    {
        return $this->userModel;
    }

    public function setUserModel(AudiobookCommentModel $userModel): void
    {
        $this->userModel = $userModel;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function isEdited(): bool
    {
        return $this->edited;
    }

    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    public function isMyComment(): bool
    {
        return $this->myComment;
    }

    public function setMyComment(bool $myComment): void
    {
        $this->myComment = $myComment;
    }

    public function getAudiobookCommentLike(): int
    {
        return $this->audiobookCommentLike;
    }

    public function setAudiobookCommentLike(int $audiobookCommentLike): void
    {
        $this->audiobookCommentLike = $audiobookCommentLike;
    }

    public function getAudiobookCommentUnlike(): int
    {
        return $this->audiobookCommentUnlike;
    }

    public function setAudiobookCommentUnlike(int $audiobookCommentUnlike): void
    {
        $this->audiobookCommentUnlike = $audiobookCommentUnlike;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getLiked(): ?bool
    {
        return $this->liked;
    }

    public function setLiked(?bool $liked): void
    {
        $this->liked = $liked;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }
}
