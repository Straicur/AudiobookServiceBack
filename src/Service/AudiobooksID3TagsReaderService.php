<?php

declare(strict_types=1);

namespace App\Service;

class AudiobooksID3TagsReaderService implements AudiobooksID3TagsReaderServiceInterface
{
    public string $fileName = "";
    public readonly \getID3 $ID3;

    public function __construct()
    {
        $this->ID3 = new \getID3();
    }

    private function analyze(): array
    {
        return $this->ID3->analyze($this->fileName);
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getTagsInfo(): array
    {
        return $this->analyze();
    }
}
