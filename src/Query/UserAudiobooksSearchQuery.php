<?php

namespace App\Query;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobooksSearchQuery
{
    #[Assert\NotNull(message: "Page is null")]
    #[Assert\NotBlank(message: "Page is empty")]
    #[Assert\Type(type: "integer")]
    private int $page;

    #[Assert\NotNull(message: "Limit is null")]
    #[Assert\NotBlank(message: "Limit is empty")]
    #[Assert\Type(type: "integer")]
    private int $limit;

    #[Assert\NotNull(message: "Title is null")]
    #[Assert\NotBlank(message: "Title is empty")]
    #[Assert\Type(type: "string")]
    private string $title;

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 0)]
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    #[OA\Property(type: "integer", example: 10)]
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}