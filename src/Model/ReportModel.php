<?php

namespace App\Model;

use OpenApi\Attributes as OA;

class ReportModel implements ModelInterface
{
    private string $id;
    private int $type;
    private ?string $description = null;
    private ?string $actionId = null;

    /**
     * @param string $id
     * @param int $type
     */
    public function __construct(string $id, int $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[OA\Property(type: "integer", enum: [1 => 'COMMENT', 2 => 'AUDIOBOOK_PROBLEM', 3 => 'CATEGORY_PROBLEM', 4 => 'SYSTEM_PROBLEM', 5 => 'USER_PROBLEM', 6 => 'SETTINGS_PROBLEM'])]
    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    public function setActionId(string $actionId): void
    {
        $this->actionId = $actionId;
    }

}