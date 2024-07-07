<?php

namespace App\Service;

interface AudiobooksID3TagsReaderServiceInterface
{
    public function setFileName(string $fileName): void;
    public function getTagsInfo(): array;
}
