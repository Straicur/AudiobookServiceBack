<?php

namespace App\Entity;

use App\Repository\AudiobookRatingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookRatingRepository::class)]
class AudiobookRating
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[ORM\ManyToOne(targetEntity: Audiobook::class, inversedBy: 'audiobookRatings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Audiobook $audiobook;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $rating;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * @param Audiobook $audiobook
     * @param bool $rating
     * @param User $user
     */
    public function __construct(Audiobook $audiobook, bool $rating, User $user)
    {
        $this->audiobook = $audiobook;
        $this->rating = $rating;
        $this->user = $user;
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function setRating(bool $rating): self
    {
        $this->rating = $rating;

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
