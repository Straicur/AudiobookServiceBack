<?php

namespace App\Entity;

use App\Repository\AudiobookUserCommentLikeRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookUserCommentLikeRepository::class)]
class AudiobookUserCommentLike
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $liked;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateAdd;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted;

    #[ORM\ManyToOne(targetEntity: AudiobookUserComment::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AudiobookUserComment $audiobookUserComment;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * @param bool $liked
     * @param AudiobookUserComment $audiobookUserComment
     * @param User $user
     */
    public function __construct(bool $liked, AudiobookUserComment $audiobookUserComment, User $user)
    {
        $this->liked = $liked;
        $this->dateAdd = new DateTime();
        $this->deleted = false;
        $this->audiobookUserComment = $audiobookUserComment;
        $this->user = $user;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getLiked(): bool
    {
        return $this->liked;
    }

    public function setLiked(bool $liked): self
    {
        $this->liked = $liked;

        return $this;
    }

    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getAudiobookUserComment(): AudiobookUserComment
    {
        return $this->audiobookUserComment;
    }

    public function setAudiobookUserComment(?AudiobookUserComment $audiobookUserComment): self
    {
        $this->audiobookUserComment = $audiobookUserComment;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
