<?php

declare(strict_types = 1);

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

class ProcedureUtil
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function executeStoredProcedure(string $procedureName, array $params = []): void
    {
        $executeString = "CALL {$procedureName}";
        $parametersString = trim(implode(', ', $params), " \t\n\r\0\x0B,");
        $this->entityManager->getConnection()->prepare("{$executeString} ({$parametersString})")->executeQuery();
    }
}
