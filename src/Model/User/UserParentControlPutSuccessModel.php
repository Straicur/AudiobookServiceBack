<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;

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