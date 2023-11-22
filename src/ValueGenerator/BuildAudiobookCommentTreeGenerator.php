<?php

namespace App\ValueGenerator;

use App\Entity\User;
use App\Model\User\AudiobookCommentsModel;
use App\Model\User\AudiobookCommentUserModel;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use Symfony\Component\Uid\Uuid;


class BuildAudiobookCommentTreeGenerator implements ValueGeneratorInterface
{
    private array $elements;
    private readonly AudiobookUserCommentRepository $audiobookUserCommentRepository;
    private readonly AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository;
    private readonly User $user;
    private bool $admin;

    /**
     * @param array $elements
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param User $user
     * @param bool $admin
     */
    public function __construct(
        array                              $elements,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        User                               $user,
        bool                               $admin
    )
    {
        $this->elements = $elements;
        $this->audiobookUserCommentRepository = $audiobookUserCommentRepository;
        $this->audiobookUserCommentLikeRepository = $audiobookUserCommentLikeRepository;
        $this->user = $user;
        $this->admin = $admin;
    }

    private function buildTree(
        array $elements,
        User  $user,
        bool  $admin,
        ?Uuid $parentId = null
    ): array
    {
        $branch = array();

        foreach ($elements as $element) {

            if ($element->getParent() == $parentId || ($element->getParent() != null && $element->getParent()->getId() == $parentId)) {

                if ($admin) {
                    $children = $this->audiobookUserCommentRepository->findBy([
                        "parent" => $element->getId()
                    ]);
                } else {
                    $children = $this->audiobookUserCommentRepository->findBy([
                        "parent" => $element->getId(),
                        "deleted" => false
                    ]);
                }

                $audiobookParentUser = $element->getUser();
                $myComment = $audiobookParentUser === $user;

                $commentLikes = $this->audiobookUserCommentLikeRepository->findBy([
                    "audiobookUserComment" => $element->getId(),
                    "deleted" => false
                ]);

                $userModel = new AudiobookCommentUserModel($audiobookParentUser->getUserInformation()->getEmail(), $audiobookParentUser->getUserInformation()->getFirstname());

                $child = new AudiobookCommentsModel(
                    $userModel,
                    $element->getId(),
                    $element->getComment(),
                    $element->getEdited(),
                    $myComment
                );

                if ($parentId != null) {
                    $child->setParentId($parentId);
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
     * @return array
     */
    private function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    private function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     */
    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }
}