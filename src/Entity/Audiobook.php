<?php

namespace App\Entity;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookRepository::class)]
class Audiobook
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToMany(targetEntity: AudiobookCategory::class, inversedBy: 'audiobooks')]
    private Collection $categories;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $author;

    #[ORM\Column(type: 'string', length: 255)]
    private string $version;

    #[ORM\Column(type: 'string', length: 255)]
    private string $album;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $year;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $encoded = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $duration;

    #[ORM\Column(type: 'string', length: 255)]
    private string $size;

    #[ORM\Column(type: 'integer')]
    private int $parts;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'integer')]
    private int $age;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateAdd;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $fileName;

    /**
     * @param string $title
     * @param string $author
     * @param string $version
     * @param string $album
     * @param \DateTime $year
     * @param string $duration
     * @param string $size
     * @param int $parts
     * @param string $description
     * @param AudiobookAgeRange $age
     * @param string $fileName
     */
    public function __construct(string $title, string $author, string $version, string $album, \DateTime $year, string $duration, string $size,int $parts, string $description, AudiobookAgeRange $age ,string $fileName)
    {
        $this->title = $title;
        $this->author = $author;
        $this->version = $version;
        $this->album = $album;
        $this->year = $year;
        $this->duration = $duration;
        $this->size = $size;
        $this->parts = $parts;
        $this->description = $description;
        $this->age = $age->value;
        $this->categories = new ArrayCollection();
        $this->active = false;
        $this->dateAdd = new \DateTime('Now');
        $this->fileName = $fileName;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @return Collection<int, AudiobookCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(AudiobookCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(AudiobookCategory $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getAlbum(): string
    {
        return $this->album;
    }

    public function setAlbum(string $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getYear(): \DateTime
    {
        return $this->year;
    }

    public function setYear(\DateTime $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getEncoded(): ?string
    {
        return $this->encoded;
    }

    public function setEncoded(?string $encoded): self
    {
        $this->encoded = $encoded;

        return $this;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function setParts(int $parts): self
    {
        $this->parts = $parts;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAge(): AudiobookAgeRange
    {
        return match ($this->age) {
            1 => AudiobookAgeRange::FROM3TO7,
            2 => AudiobookAgeRange::FROM7TO12,
            3 => AudiobookAgeRange::FROM12TO16,
            4 => AudiobookAgeRange::FROM16TO18,
            5 => AudiobookAgeRange::ABOVE18,
        };
    }

    public function setAge(AudiobookAgeRange $age): self
    {
        $this->age = $age->value;

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

    public function getDateAdd(): ?\DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTime $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
