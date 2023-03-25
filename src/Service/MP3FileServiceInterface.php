<?php

namespace App\Service;

interface MP3FileServiceInterface
{
    public function configure(string $fileName): void;

    public function getDuration(): int;
}