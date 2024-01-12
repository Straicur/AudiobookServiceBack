<?php

namespace App\Model\User;

use App\Model\Error\ModelInterface;

class UserParentControlPutSuccessModel implements ModelInterface
{
    private string $smsCode;

    /**
     * @param string $smsCode
     */
    public function __construct(string $smsCode)
    {
        $this->smsCode = $smsCode;
    }

    public function getSmsCode(): string
    {
        return $this->smsCode;
    }

    public function setSmsCode(string $smsCode): void
    {
        $this->smsCode = $smsCode;
    }

}