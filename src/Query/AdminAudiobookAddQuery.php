<?php

namespace App\Query;

use Symfony\Component\Validator\Constraints as Assert;

class AdminAudiobookAddQuery
{
    #[Assert\NotNull(message: "HashName is null")]
    #[Assert\NotBlank(message: "HashName is empty")]
    #[Assert\Type(type: "string")]
    private string $hashName;

    #[Assert\NotNull(message: "FileName is null")]
    #[Assert\NotBlank(message: "FileName is empty")]
    #[Assert\Type(type: "string")]
    private string $fileName;

    #[Assert\NotNull(message: "Base64 is null")]
    #[Assert\NotBlank(message: "Base64 is empty")]
    #[Assert\Type(type: "string")]
    private string $base64;

    #[Assert\NotNull(message: "Part is null")]
    #[Assert\NotBlank(message: "Part is empty")]
    #[Assert\Type(type: "integer")]
    private int $part;

    #[Assert\NotNull(message: "Parts is null")]
    #[Assert\NotBlank(message: "Parts is empty")]
    #[Assert\Type(type: "integer")]
    private int $parts;

    /**
     * @return string
     */
    public function getHashName(): string
    {
        return $this->hashName;
    }

    /**
     * @param string $hashName
     */
    public function setHashName(string $hashName): void
    {
        $this->hashName = $hashName;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getBase64(): string
    {
        return $this->base64;
    }

    /**
     * @param string $base64
     */
    public function setBase64(string $base64): void
    {
        $this->base64 = $base64;
    }

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    /**
     * @return int
     */
    public function getParts(): int
    {
        return $this->parts;
    }

    /**
     * @param int $parts
     */
    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }

}