<?php

namespace App\Entity;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AudiobookRepository::class)]
class Audiobook
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToMany(targetEntity: AudiobookCategory::class, inversedBy: 'audiobooks')]
    private Collection $categories;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $author;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $version;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $album;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $year;

    #[ORM\Column(Types::STRING, length: 255, nullable: true)]
    private ?string $encoded = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $duration;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $size;

    #[ORM\Column(type: Types::INTEGER)]
    private int $parts;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::INTEGER)]
    private int $age;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateAdd;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $fileName;

    #[ORM\OneToMany(targetEntity: AudiobookRating::class, mappedBy: 'audiobook')]
    private Collection $audiobookRatings;
    #[ORM\OneToMany(targetEntity: AudiobookUserComment::class, mappedBy: 'audiobook')]
    private Collection $audiobookUserComments;
    #[ORM\OneToMany(targetEntity: AudiobookInfo::class, mappedBy: 'audiobook')]
    private Collection $audiobookInfos;

    #[ORM\Column(type: Types::FLOAT)]
    private float $avgRating;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $imgFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $imgFileChangeDate = null;

    /**
     * @param string $title
     * @param string $author
     * @param string $version
     * @param string $album
     * @param DateTime $year
     * @param int $duration
     * @param string $size
     * @param int $parts
     * @param string $description
     * @param AudiobookAgeRange $age
     * @param string $fileName
     */
    public function __construct(
        string $title,
        string $author,
        string $version,
        string $album,
        DateTime $year,
        int $duration,
        string $size,
        int $parts,
        string $description,
        AudiobookAgeRange $age,
        string $fileName
    ) {
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
        $this->dateAdd = new DateTime();
        $this->fileName = $fileName;
        $this->audiobookRatings = new ArrayCollection();
        $this->audiobookUserComments = new ArrayCollection();
        $this->audiobookInfos = new ArrayCollection();
        $this->avgRating = 0;
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

    public function getYear(): DateTime
    {
        return $this->year;
    }

    public function setYear(DateTime $year): self
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

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
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

    public function getDateAdd(): ?DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): self
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

    /**
     * @return Collection<int, AudiobookRating>
     */
    public function getAudiobookRatings(): Collection
    {
        return $this->audiobookRatings;
    }

    public function addAudiobookRating(AudiobookRating $AudiobookRating): self
    {
        if (!$this->audiobookRatings->contains($AudiobookRating)) {
            $this->audiobookRatings[] = $AudiobookRating;
            $AudiobookRating->setAudiobook($this);
        }

        return $this;
    }

    public function removeAudiobookRating(AudiobookRating $AudiobookRating): self
    {
        if ($this->audiobookRatings->removeElement($AudiobookRating)) {
            // set the owning side to null (unless already changed)
            if ($AudiobookRating->getAudiobook() === $this) {
                $AudiobookRating->setAudiobook(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AudiobookUserComment>
     */
    public function getAudiobookUserComments(): Collection
    {
        return $this->audiobookUserComments;
    }

    public function addAudiobookUserComment(AudiobookUserComment $audiobookUserComment): self
    {
        if (!$this->audiobookUserComments->contains($audiobookUserComment)) {
            $this->audiobookUserComments[] = $audiobookUserComment;
            $audiobookUserComment->setAudiobook($this);
        }

        return $this;
    }

    public function removeAudiobookUserComment(AudiobookUserComment $audiobookUserComment): self
    {
        if ($this->audiobookUserComments->removeElement($audiobookUserComment)) {
            // set the owning side to null (unless already changed)
            if ($audiobookUserComment->getAudiobook() === $this) {
                $audiobookUserComment->setAudiobook(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AudiobookInfo>
     */
    public function getAudiobookInfos(): Collection
    {
        return $this->audiobookUserComments;
    }

    public function addAudiobookInfo(AudiobookInfo $audiobookInfo): self
    {
        if (!$this->audiobookInfos->contains($audiobookInfo)) {
            $this->audiobookInfos[] = $audiobookInfo;
            $audiobookInfo->setAudiobook($this);
        }

        return $this;
    }

    public function removeAudiobookInfo(AudiobookInfo $audiobookInfo): self
    {
        if ($this->audiobookInfos->removeElement($audiobookInfo)) {
            // set the owning side to null (unless already changed)
            if ($audiobookInfo->getAudiobook() === $this) {
                $audiobookInfo->setAudiobook(null);
            }
        }

        return $this;
    }

    public function getAvgRating(): float
    {
        return $this->avgRating;
    }

    public function setAvgRating(float $avgRating): self
    {
        $this->avgRating = $avgRating;

        return $this;
    }

    public function getImgFile(): ?string
    {
        return $this->imgFile;
    }

    public function setImgFile(?string $imgFile): static
    {
        $this->imgFile = $imgFile;

        return $this;
    }

    public function getImgFileChangeDate(): ?DateTime
    {
        return $this->imgFileChangeDate;
    }

    public function setImgFileChangeDate(): static
    {
        $this->imgFileChangeDate = new DateTime();

        return $this;
    }
}
