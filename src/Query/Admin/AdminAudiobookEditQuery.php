<?php

namespace App\Query\Admin;

use App\Enums\AudiobookAgeRange;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminAudiobookEditQuery
{
    #[Assert\NotNull(message: 'AudiobookId is null')]
    #[Assert\NotBlank(message: 'AudiobookId is blank')]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: 'Title is null')]
    #[Assert\NotBlank(message: 'Title is empty')]
    #[Assert\Type(type: 'string')]
    private string $title;

    #[Assert\NotNull(message: 'Author is null')]
    #[Assert\NotBlank(message: 'Author is empty')]
    #[Assert\Type(type: 'string')]
    private string $author;

    #[Assert\NotNull(message: 'Version is null')]
    #[Assert\NotBlank(message: 'Version is empty')]
    #[Assert\Type(type: 'string')]
    private string $version;

    #[Assert\NotNull(message: 'Album is null')]
    #[Assert\NotBlank(message: 'Album is empty')]
    #[Assert\Type(type: 'string')]
    private string $album;

    #[Assert\NotNull(message: 'Year is null')]
    #[Assert\NotBlank(message: 'Year is blank')]
    #[Assert\Type(type: 'datetime')]
    private DateTime $year;

    #[Assert\NotNull(message: 'Duration is null')]
    #[Assert\NotBlank(message: 'Duration is empty')]
    #[Assert\Type(type: 'string')]
    private string $duration;

    #[Assert\NotNull(message: 'Size is null')]
    #[Assert\NotBlank(message: 'Size is empty')]
    #[Assert\Type(type: 'string')]
    private string $size;

    #[Assert\NotNull(message: 'Parts is null')]
    #[Assert\NotBlank(message: 'Parts is empty')]
    #[Assert\Type(type: 'integer')]
    private int $parts;

    #[Assert\NotNull(message: 'Description is null')]
    #[Assert\NotBlank(message: 'Description is empty')]
    #[Assert\Type(type: 'string')]
    private string $description;

    #[Assert\NotNull(message: 'Age is null')]
    #[Assert\NotBlank(message: 'Age is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min: 1,
        max: 5,
    )]
    private int $age;

    #[Assert\NotNull(message: 'Encoded is null')]
    #[Assert\NotBlank(message: 'Encoded is empty')]
    #[Assert\Type(type: 'string')]
    private string $encoded;

    /**
     * @return Uuid
     */
    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getAlbum(): string
    {
        return $this->album;
    }

    public function setAlbum(string $album): void
    {
        $this->album = $album;
    }

    #[OA\Property(property: 'year', example: 'd.m.Y')]
    public function getYear(): DateTime
    {
        return $this->year;
    }

    public function setYear(string $year): void
    {
        $this->year = DateTime::createFromFormat('d.m.Y', $year);
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAge(): AudiobookAgeRange
    {
        return match ($this->age) {
            1 => AudiobookAgeRange::FROM3TO7,
            2 => AudiobookAgeRange::FROM7TO12,
            3 => AudiobookAgeRange::FROM12TO16,
            4 => AudiobookAgeRange::FROM16TO18,
            5 => AudiobookAgeRange::ABOVE18
        };
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function getEncoded(): string
    {
        return $this->encoded;
    }

    public function setEncoded(string $encoded): void
    {
        $this->encoded = $encoded;
    }

}