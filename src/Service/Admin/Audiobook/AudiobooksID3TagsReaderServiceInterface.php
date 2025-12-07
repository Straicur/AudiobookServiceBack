<?php

declare(strict_types = 1);

namespace App\Service\Admin\Audiobook;

interface AudiobooksID3TagsReaderServiceInterface
{
    public function setFileName(string $fileName): void;

    public function getTagsInfo(): array;
}
