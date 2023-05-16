<?php

namespace App\Entity;

use App\Repository\AudiobookInfoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookInfoRepository::class)]
class AudiobookInfo
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Audiobook::class, inversedBy: 'audiobookInfos')]
    #[ORM\JoinColumn(nullable: false)]
    private Audiobook $audiobook;

    #[ORM\Column(type: 'integer')]
    private int $part;

    #[ORM\Column(type: 'string', length: 255)]
    private string $endedTime;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $watchingDate;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'boolean')]
    private bool $watched;

    /**
     * @param User $user
     * @param Audiobook $audiobook
     * @param int $part
     * @param string $endedTime
     * @param bool $watched
     */
    public function __construct(User $user, Audiobook $audiobook, int $part, string $endedTime, bool $watched)
    {
        $this->user = $user;
        $this->audiobook = $audiobook;
        $this->part = $part;
        $this->endedTime = $endedTime;
        $this->watchingDate = new \DateTime('Now');
        $this->active = true;
        $this->watched = $watched;
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getAudiobook(): Audiobook
    {
        return $this->audiobook;
    }

    public function setAudiobook(Audiobook $audiobook): self
    {
        $this->audiobook = $audiobook;

        return $this;
    }

    public function getPart(): int
    {
        return $this->part;
    }

    public function setPart(int $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getEndedTime(): string
    {
        return $this->endedTime;
    }

    public function setEndedTime(string $endedTime): self
    {
        $this->endedTime = $endedTime;

        return $this;
    }

    public function getWatchingDate(): \DateTime
    {
        return $this->watchingDate;
    }

    public function setWatchingDate(\DateTime $watchingDate): self
    {
        $this->watchingDate = $watchingDate;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getWatched(): bool
    {
        return $this->watched;
    }

    public function setWatched(bool $watched): self
    {
        $this->watched = $watched;

        return $this;
    }
}
