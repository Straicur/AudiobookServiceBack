<?php

namespace App\Model;

/**
 * TenzieAllSuccessModel
 *
 */
class TenzieAllSuccessModel implements ModelInterface
{
    /**
     * @var TenzieAllModel[]
     */
    private array $tenzieAllModels = [];

    /**
     * @return array
     */
    public function getTenzieAllModels(): array
    {
        return $this->tenzieAllModels;
    }

    public function addTenzieAllModel(TenzieAllModel $tenzieAllModel)
    {
        $this->tenzieAllModels[] = $tenzieAllModel;
    }

    /**
     * @param array $tenzieAllModels
     */
    public function setTenzieAllModels(array $tenzieAllModels): void
    {
        $this->tenzieAllModels = $tenzieAllModels;
    }

}