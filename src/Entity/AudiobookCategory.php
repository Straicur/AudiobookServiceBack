<?php

namespace App\Entity;

use App\Repository\AudiobookCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookCategoryRepository::class)]
class AudiobookCategory
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private ?AudiobookCategory $parent = null;

    #[ORM\ManyToMany(targetEntity: Audiobook::class, mappedBy: 'categories')]
    private ArrayCollection $audiobooks;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->audiobooks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getAudiobookCategories(): Collection
    {
        return $this->audiobookCategories;
    }

    public function addAudiobookCategory(self $audiobookCategory): self
    {
        if (!$this->audiobookCategories->contains($audiobookCategory)) {
            $this->audiobookCategories[] = $audiobookCategory;
            $audiobookCategory->setParent($this);
        }

        return $this;
    }

    public function removeAudiobookCategory(self $audiobookCategory): self
    {
        if ($this->audiobookCategories->removeElement($audiobookCategory)) {
            // set the owning side to null (unless already changed)
            if ($audiobookCategory->getParent() === $this) {
                $audiobookCategory->setParent(null);
            }
        }

        return $this;
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
            $audiobook->addCategory($this);
        }

        return $this;
    }

    public function removeAudiobook(Audiobook $audiobook): self
    {
        if ($this->audiobooks->removeElement($audiobook)) {
            $audiobook->removeCategory($this);
        }

        return $this;
    }

}
