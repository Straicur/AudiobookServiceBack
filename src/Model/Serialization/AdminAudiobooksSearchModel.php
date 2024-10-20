<?php

declare(strict_types=1);

namespace App\Model\Serialization;

use DateTime;

class AdminAudiobooksSearchModel
{
    public ?array $categories = [];
    public ?string $author = null;
    public ?string $title = null;
    public ?string $album = null;
    public ?int $duration = null;
    public ?int $age = null;
    public ?DateTime $year = null;
    public ?int $parts = null;
    public ?int $order = null;

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function setCategories(?array $categories): void
    {
        $this->categories = $categories;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(?string $album): void
    {
        $this->album = $album;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): void
    {
        $this->age = $age;
    }

    public function getYear(): ?DateTime
    {
        return $this->year;
    }

    public function setYear(?string $year): void
    {
        if (DateTime::createFromFormat('d.m.Y', $year)) {
            $this->year = DateTime::createFromFormat('d.m.Y', $year);
        }
    }

    public function getParts(): ?int
    {
        return $this->parts;
    }

    public function setParts(?int $parts): void
    {
        $this->parts = $parts;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }

}