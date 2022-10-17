<?php

namespace App\Query;

use App\Controller\TenziesController;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TenzieAdd Query
 *
 * @see TenziesController::tenzieAdd()
 *
 */
class TenzieAddQuery
{

    #[Assert\NotNull(message: "Title is null")]
    #[Assert\NotBlank(message: "Title is empty")]
    #[Assert\Type(type: "string")]
    private string $title;

    #[Assert\NotNull(message: "Level is null")]
    #[Assert\NotBlank(message: "Level is empty")]
    #[Assert\Type(type: "string")]
    private string $level;

    #[Assert\NotNull(message: "Time is null")]
    #[Assert\NotBlank(message: "Time is empty")]
    #[Assert\Type(type: "string")]
    private string $time;

    #[Assert\NotNull(message: "DateAdd is null")]
    #[Assert\NotBlank(message: "DateAdd is blank")]
    #[Assert\Type(type: "datetime")]
    private DateTime $dateAdd;

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

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return DateTime
     */
    #[OA\Property(property: "dateAdd", example: "d.m.Y")]
    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    /**
     * @param string $dateAdd
     */
    public function setDateAdd(string $dateAdd): void
    {
        $this->dateAdd = DateTime::createFromFormat('d.m.Y', $dateAdd);
    }
}