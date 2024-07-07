<?php

namespace App\Service;

use App\Query\Admin\AdminAudiobookAddQuery;
use App\Query\Admin\AdminAudiobookReAddingQuery;

interface AudiobookServiceInterface
{
    public function configure(AdminAudiobookAddQuery|AdminAudiobookReAddingQuery $query): void;

    public function checkAndAddFile(): void;

    public function lastFile(): bool;

    public function combineFiles(): void;

    public function unzip(string $reAdding = null): string;

    public function createAudiobookJsonData(string $folderDir): array;

    public function removeFolder(string $dir): bool;
}
