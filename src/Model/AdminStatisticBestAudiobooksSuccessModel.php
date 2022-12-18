<?php

namespace App\Model;

class AdminStatisticBestAudiobooksSuccessModel implements ModelInterface
{
    private ?AdminAudiobookDetailsModel $firstAudiobook;
    private ?AdminAudiobookDetailsModel $secondAudiobook;
    private ?AdminAudiobookDetailsModel $thirdAudiobook;

    /**
     * @param AdminAudiobookDetailsModel|null $firstAudiobook
     * @param AdminAudiobookDetailsModel|null $secondAudiobook
     * @param AdminAudiobookDetailsModel|null $thirdAudiobook
     */
    public function __construct(?AdminAudiobookDetailsModel $firstAudiobook = null, ?AdminAudiobookDetailsModel $secondAudiobook = null, ?AdminAudiobookDetailsModel $thirdAudiobook = null)
    {
        $this->firstAudiobook = $firstAudiobook;
        $this->secondAudiobook = $secondAudiobook;
        $this->thirdAudiobook = $thirdAudiobook;
    }

    /**
     * @return AdminAudiobookDetailsModel|null
     */
    public function getFirstAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->firstAudiobook;
    }

    /**
     * @param AdminAudiobookDetailsModel $firstAudiobook
     */
    public function setFirstAudiobook(AdminAudiobookDetailsModel $firstAudiobook): void
    {
        $this->firstAudiobook = $firstAudiobook;
    }

    /**
     * @return AdminAudiobookDetailsModel|null
     */
    public function getSecondAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->secondAudiobook;
    }

    /**
     * @param AdminAudiobookDetailsModel $secondAudiobook
     */
    public function setSecondAudiobook(AdminAudiobookDetailsModel $secondAudiobook): void
    {
        $this->secondAudiobook = $secondAudiobook;
    }

    /**
     * @return AdminAudiobookDetailsModel|null
     */
    public function getThirdAudiobook(): ?AdminAudiobookDetailsModel
    {
        return $this->thirdAudiobook;
    }

    /**
     * @param AdminAudiobookDetailsModel $thirdAudiobook
     */
    public function setThirdAudiobook(AdminAudiobookDetailsModel $thirdAudiobook): void
    {
        $this->thirdAudiobook = $thirdAudiobook;
    }

}