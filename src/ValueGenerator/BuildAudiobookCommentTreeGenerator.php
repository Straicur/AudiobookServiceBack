<?php

declare(strict_types=1);

namespace App\ValueGenerator;

use App\Entity\User;
use App\Model\Common\AudiobookCommentModel;
use App\Model\Common\AudiobookCommentsModel;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use Symfony\Component\Uid\Uuid;

class BuildAudiobookCommentTreeGenerator implements ValueGeneratorInterface
{
    public function __construct(
        private array $elements,
        private readonly AudiobookUserCommentRepository $audiobookUserCommentRepository,
        private readonly AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        private User $user,
        private bool $admin,
    ) {
    }

    private function buildTree(
        array $elements,
        User $user,
        bool $admin,
        ?Uuid $parentId = null,
    ): array {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->getParent() === $parentId || ($element->getParent() !== null && $element->getParent()->getId() === $parentId)) {
                if ($admin) {
                    $children = $this->audiobookUserCommentRepository->findBy([
                        'parent' => $element->getId(),
                    ]);
                } else {
                    $children = $this->audiobookUserCommentRepository->findBy([
                        'parent'  => $element->getId(),
                        'deleted' => false,
                    ]);
                }

                $audiobookParentUser = $element->getUser();
                $myComment = $audiobookParentUser === $user;

                $commentLikes = $this->audiobookUserCommentLikeRepository->findBy([
                    'audiobookUserComment' => $element->getId(),
                    'deleted'              => false,
                ]);

                $userModel = new AudiobookCommentModel(
                    $audiobookParentUser->getUserInformation()->getEmail(),
                    $audiobookParentUser->getUserInformation()->getFirstname()
                );

                $child = new AudiobookCommentsModel(
                    $userModel,
                    (string)$element->getId(),
                    $element->getComment(),
                    $element->getEdited(),
                    $myComment,
                );

                if ($parentId !== null) {
                    $child->setParentId((string)$parentId);
                }

                if ($admin) {
                    $child->setDeleted($element->getDeleted());
                }

                $userLike = null;

                $likes = 0;
                $unlikes = 0;

                foreach ($commentLikes as $commentLike) {
                    if ($commentLike->getLiked()) {
                        $likes = +1;
                    } else {
                        $unlikes = +1;
                    }
                    if ($commentLike->getUser()->getId() === $user->getId()) {
                        $userLike = $commentLike->getLiked();
                    }
                }

                $child->setAudiobookCommentLike($likes);
                $child->setAudiobookCommentUnlike($unlikes);

                if (!$admin) {
                    $child->setLiked($userLike);
                }

                if (!empty($children)) {
                    $children = $this->buildTree($children, $user, $admin, $element->getId());

                    foreach ($children as $parentChild) {
                        $child->addChildren($parentChild);
                    }
                }

                $branch[] = $child;
            }
        }

        return $branch;
    }

    public function generate(): array
    {
        return $this->buildTree($this->getElements(), $this->getUser(), $this->isAdmin());
    }

    /**
     * @return AudiobookCommentsModel[]
     */
    private function getElements(): array
    {
        return $this->elements;
    }

    private function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }
}
