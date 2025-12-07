<?php

declare(strict_types = 1);

namespace App\Query\Admin;

interface AdminAudiobookAddFileInterface
{
    public function getHashName(): string;

    public function getFileName(): string;

    public function getPart(): int;

    public function getParts(): int;

    public function getBase64(): string;
}
