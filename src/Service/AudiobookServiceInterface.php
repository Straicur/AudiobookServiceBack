<?php

namespace App\Service;

use App\Query\AdminAudiobookAddQuery;
use App\Query\AdminAudiobookReAddingQuery;

interface AudiobookServiceInterface
{
    public function configure(AdminAudiobookAddQuery|AdminAudiobookReAddingQuery $query): void;

    public function checkAndAddFile(): void;

    public function lastFile(): bool;

    public function combineFiles(): void;

    public function unzip(bool $reAdding = false): string;

    public function createAudiobookJsonData(string $folderDir): array;

    public function removeFolder(string $dir): bool;

}