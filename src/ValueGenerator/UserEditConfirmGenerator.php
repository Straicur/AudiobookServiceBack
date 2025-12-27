<?php

declare(strict_types = 1);

namespace App\ValueGenerator;

use Override;

use function strlen;

class UserEditConfirmGenerator implements ValueGeneratorInterface
{
    #[Override]
    public function generate(): string
    {
        $chars = 'QWERTYUIOPASDFGHJKLZXCVBNM1234567890';

        mt_srand(time());

        $code = '';

        for ($i = 0; 8 > $i; ++$i) {
            $num = mt_rand() % (strlen($chars) - 1);
            $code .= $chars[$num];
        }

        return $code;
    }
}
