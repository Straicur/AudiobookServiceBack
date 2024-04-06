<?php

namespace App\Entity;

use App\Repository\AudiobookCategoryRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use DateTime;
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
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Audiobook::class, mappedBy: 'categories')]
    private Collection $audiobooks;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $categoryKey;

    #[ORM\ManyToOne(targetEntity: AudiobookCategory::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?AudiobookCategory $parent = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateAdd;

    /**
     * @param string $name
     * @param ValueGeneratorInterface $categoryKeyGenerator
     */
    public function __construct(string $name, ValueGeneratorInterface $categoryKeyGenerator)
    {
        $this->name = $name;
        $this->active = false;
        $this->audiobooks = new ArrayCollection();
        $this->categoryKey = $categoryKeyGenerator->generate();
        $this->dateAdd = new DateTime();
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

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(ValueGeneratorInterface $categoryKeyGenerator): self
    {
        $this->categoryKey = $categoryKeyGenerator->generate();

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
     * @return DateTime
     */
    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    /**
     * @param DateTime $dateAdd
     */
    public function setDateAdd(DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd;
    }

}
