<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\Common\AudiobookCommentModel;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class AdminReportAudiobookCommentsModel
{
    private ?bool $deleted = null;

    private ?string $parentId = null;

    /**
     * @var AdminReportAudiobookCommentsModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AdminReportAudiobookCommentsModel::class))
    )]
    private array $children = [];

    public function __construct(private AudiobookCommentModel $userModel, private string $comment, private bool $isReportedComment = false) {}

    /**
     * @return AdminReportAudiobookCommentsModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(AdminReportAudiobookCommentsModel $children): void
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

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function isReportedComment(): bool
    {
        return $this->isReportedComment;
    }

    public function setIsReportedComment(bool $isReportedComment): void
    {
        $this->isReportedComment = $isReportedComment;
    }
}
