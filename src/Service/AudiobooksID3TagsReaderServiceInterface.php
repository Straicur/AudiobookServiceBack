<?php

namespace App\Service;

interface AudiobooksID3TagsReaderServiceInterface
{
    public function getTagsInfo(string $path): array;
}