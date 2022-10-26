<?php

namespace App\Query;

use DateTime;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class AdminAudiobookEditQuery
{
    #[Assert\NotNull(message: "AudiobookId is null")]
    #[Assert\NotBlank(message: "AudiobookId is blank")]
    #[Assert\Uuid]
    private Uuid $audiobookId;

    #[Assert\NotNull(message: "Title is null")]
    #[Assert\NotBlank(message: "Title is empty")]
    #[Assert\Type(type: "string")]
    private string $title;

    #[Assert\NotNull(message: "Author is null")]
    #[Assert\NotBlank(message: "Author is empty")]
    #[Assert\Type(type: "string")]
    private string $author;

    #[Assert\NotNull(message: "Version is null")]
    #[Assert\NotBlank(message: "Version is empty")]
    #[Assert\Type(type: "string")]
    private string $version;

    #[Assert\NotNull(message: "Album is null")]
    #[Assert\NotBlank(message: "Album is empty")]
    #[Assert\Type(type: "string")]
    private string $album;

    #[Assert\NotNull(message: "Year is null")]
    #[Assert\NotBlank(message: "Year is blank")]
    #[Assert\Type(type: "datetime")]
    private \DateTime $year;

    #[Assert\NotNull(message: "Duration is null")]
    #[Assert\NotBlank(message: "Duration is empty")]
    #[Assert\Type(type: "string")]
    private string $duration;

    #[Assert\NotNull(message: "Size is null")]
    #[Assert\NotBlank(message: "Size is empty")]
    #[Assert\Type(type: "string")]
    private string $size;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "integer")]
    private int $parts;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "string")]
    private string $description;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "integer")]
    private int $age;

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getAudiobookId(): Uuid
    {
        return $this->audiobookId;
    }

    /**
     * @param string $audiobookId
     */
    public function setAudiobookId(string $audiobookId): void
    {
        $this->audiobookId = Uuid::fromString($audiobookId);;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAlbum(): string
    {
        return $this->album;
    }

    /**
     * @param string $album
     */
    public function setAlbum(string $album): void
    {
        $this->album = $album;
    }

    /**
     * @return \DateTime
     */
    #[OA\Property(property: "year", example: "d.m.Y")]
    public function getYear(): \DateTime
    {
        return $this->year;
    }

    /**
     * @param string $year
     */

    public function setYear(string $year): void
    {
        $this->year = DateTime::createFromFormat('d.m.Y', $year);
    }

    /**
     * @return string
     */
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getParts(): int
    {
        return $this->parts;
    }

    /**
     * @param int $parts
     */
    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age): void
    {
        $this->age = $age;
    }

}