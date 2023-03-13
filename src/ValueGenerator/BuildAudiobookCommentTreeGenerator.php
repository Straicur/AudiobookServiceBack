<?php

namespace App\ValueGenerator;

use App\Entity\User;
use App\Model\AudiobookCommentLikeModel;
use App\Model\AudiobookCommentsModel;
use App\Model\AudiobookCommentUserModel;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Repository\AudiobookUserCommentRepository;
use Symfony\Component\Uid\Uuid;


class BuildAudiobookCommentTreeGenerator implements ValueGeneratorInterface
{
    private array $elements;
    private AudiobookUserCommentRepository $audiobookUserCommentRepository;
    private AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository;
    private User $user;
    private bool $admin;

    /**
     * @param array $elements
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     * @param User $user
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
        array                              $elements,
        AudiobookUserCommentRepository     $audiobookUserCommentRepository,
        AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository,
        User                               $user,
        bool                               $admin,
        ?Uuid                              $parentId = null
    ): array
    {
        $branch = array();

        foreach ($elements as $element) {

            if ($element->getParent() == $parentId || ($element->getParent() != null && $element->getParent()->getId() == $parentId)) {

                if ($admin) {
                    $children = $audiobookUserCommentRepository->findBy([
                        "parent" => $element->getId()
                    ]);
                } else {
                    $children = $audiobookUserCommentRepository->findBy([
                        "parent" => $element->getId(),
                        "deleted" => false
                    ]);
                }

                $audiobookParentUser = $element->getUser();
                $myComment = $audiobookParentUser === $user;

                $commentLikes = $audiobookUserCommentLikeRepository->findBy([
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

                if ($admin) {
                    $child->setDeleted($element->getDeleted());
                }

                foreach ($commentLikes as $commentLike) {
                    if ($commentLike->getLiked()) {
                        $child->addAudiobookCommentModel(new AudiobookCommentLikeModel(
                            $commentLike->getId(),
                            $commentLike->getLiked()
                        ));
                    } else {
                        $child->addAudiobookCommentUnlikeModel(new AudiobookCommentLikeModel(
                            $commentLike->getId(),
                            $commentLike->getLiked()
                        ));
                    }
                }

                if (!empty($children)) {

                    $children = $this->buildTree($children, $audiobookUserCommentRepository, $audiobookUserCommentLikeRepository, $user, $admin, $element->getId());

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
        return $this->buildTree($this->getElements(), $this->getAudiobookUserCommentRepository(), $this->getAudiobookUserCommentLikeRepository(), $this->getUser(), $this->isAdmin());
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
     * @return AudiobookUserCommentRepository
     */
    public function getAudiobookUserCommentRepository(): AudiobookUserCommentRepository
    {
        return $this->audiobookUserCommentRepository;
    }

    /**
     * @param AudiobookUserCommentRepository $audiobookUserCommentRepository
     */
    public function setAudiobookUserCommentRepository(AudiobookUserCommentRepository $audiobookUserCommentRepository): void
    {
        $this->audiobookUserCommentRepository = $audiobookUserCommentRepository;
    }

    /**
     * @return AudiobookUserCommentLikeRepository
     */
    public function getAudiobookUserCommentLikeRepository(): AudiobookUserCommentLikeRepository
    {
        return $this->audiobookUserCommentLikeRepository;
    }

    /**
     * @param AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository
     */
    public function setAudiobookUserCommentLikeRepository(AudiobookUserCommentLikeRepository $audiobookUserCommentLikeRepository): void
    {
        $this->audiobookUserCommentLikeRepository = $audiobookUserCommentLikeRepository;
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