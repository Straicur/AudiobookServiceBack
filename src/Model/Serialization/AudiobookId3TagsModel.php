<?php

declare(strict_types=1);

namespace App\Model\Serialization;

use DateTime;

class AudiobookId3TagsModel
{
    public ?string $album = null;
    public ?string $artist = null;
    public ?string $comment = null;
    public ?string $version = '1';
    public ?string $title = null;
    public ?string $encoded = null;
    public ?DateTime $year = null;
    public ?int $duration = null;
    public ?string $size = null;
    public ?int $parts = 0;
    public ?string $imgFileDir = null;

    public function __construct(
    ) {
        $this->year = new DateTime();
    }

    public function getAlbum(): string
    {

        return $this->album;
    }

    public function setAlbum(?string $album): void
    {
        if ($album === null) {
            $this->album = '';
            return;
        }

        $this->album = $album;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): void
    {
        $this->artist = $artist;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        if ($comment === null) {
            $this->comment = '';
            return;
        }

        $this->comment = $comment;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getEncoded(): ?string
    {
        return $this->encoded;
    }

    public function setEncoded(?string $encoded): void
    {
        $this->encoded = $encoded;
    }

    public function getYear(): DateTime
    {
        return $this->year;
    }

    public function setYear(?string $year): void
    {
        $testYear = '01.01.' . $year;
        if (empty($year) || !DateTime::createFromFormat('d.m.Y', $testYear)) {
            return;
        }

        $this->year = DateTime::createFromFormat('d.m.Y', $year);
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): void
    {
        if ($duration === null) {
            $this->duration = 0;
            return;
        }

        $this->duration = $duration;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(?string $size): void
    {
        if ($size === null) {
            $this->size = '1';
            return;
        }

        $this->size = $size;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function setParts(?int $parts): void
    {
        if ($parts === null) {
            $this->parts = 1;
            return;
        }

        $this->parts = $parts;
    }

    public function getImgFileDir(): string
    {
        return $this->imgFileDir;
    }

    public function setImgFileDir(?string $imgFileDir): void
    {
        if ($imgFileDir === null) {
            $this->imgFileDir = '';
            return;
        }

        $this->imgFileDir = $imgFileDir;
    }
}
