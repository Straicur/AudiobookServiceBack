<?php

namespace App\Service\Admin;

interface AudiobooksID3TagsReaderServiceInterface
{
    public function setFileName(string $fileName): void;
    public function getTagsInfo(): array;
}
