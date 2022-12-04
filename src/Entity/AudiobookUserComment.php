<?php

namespace App\Entity;

use App\Repository\AudiobookUserCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookUserCommentRepository::class)]
class AudiobookUserComment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 1000)]
    private string $comment;

    #[ORM\ManyToOne(targetEntity: Audiobook::class, inversedBy: 'audiobookUserComments')]
    #[ORM\JoinColumn(nullable: false)]
    private Audiobook $audiobook;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?AudiobookUserComment $parent = null;


    #[ORM\Column(type: 'boolean')]
    private bool $deleted;

    /**
     * @param string $comment
     * @param Audiobook $audiobook
     * @param User|null $user
     * @param AudiobookUserComment $parent
     */
    public function __construct(string $comment, Audiobook $audiobook, ?User $user, AudiobookUserComment $parent)
    {
        $this->comment = $comment;
        $this->audiobook = $audiobook;
        $this->user = $user;
        $this->parent = $parent;
        $this->deleted = false;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAudiobook(): Audiobook
    {
        return $this->audiobook;
    }

    public function setAudiobook(Audiobook $audiobook): self
    {
        $this->audiobook = $audiobook;

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent): self
    {
        $this->parent = $parent;

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
}
