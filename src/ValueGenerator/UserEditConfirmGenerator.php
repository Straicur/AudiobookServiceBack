<?php

declare(strict_types=1);

namespace App\ValueGenerator;

class UserEditConfirmGenerator implements ValueGeneratorInterface
{
    public function generate(): string
    {
        $chars = 'QWERTYUIOPASDFGHJKLZXCVBNM1234567890';

        mt_srand(time());

        $code = "";

        for ($i = 0; $i < 8; $i++) {
            $num = mt_rand() % (strlen($chars) - 1);
            $code .= $chars[$num];
        }
        return $code;
    }
}
