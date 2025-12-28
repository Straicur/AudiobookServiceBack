<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\ProposedAudiobooksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProposedAudiobooksRepository::class)]
class ProposedAudiobooks
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToMany(targetEntity: Audiobook::class)]
    private Collection $audiobooks;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'proposedAudiobooks')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->audiobooks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Audiobook>
     */
    public function getAudiobooks(): Collection
    {
        return $this->audiobooks;
    }

    public function addAudiobook(Audiobook $audiobook): self
    {
        if (!$this->audiobooks->contains($audiobook)) {
            $this->audiobooks[] = $audiobook;
        }

        return $this;
    }

    public function removeAudiobook(Audiobook $audiobook): self
    {
        $this->audiobooks->removeElement($audiobook);

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
