<?php

declare(strict_types=1);

namespace App\Tool;

use App\Entity\User;
use App\Enums\AudiobookAgeRange;
use DateTime;

class UserParentalControlTool
{
    /**
     * @param User $user
     * @return AudiobookAgeRange|null
     */
    public function getUserAudiobookAgeValue(User $user): ?AudiobookAgeRange
    {
        $birthday = $user->getUserInformation()->getBirthday();

        if($birthday === null){
            return null;
        }

        $today = new DateTime();

        $age = $today->diff($birthday)->y;

        if ($age >= 3 && $age < 7) {
            return AudiobookAgeRange::FROM3TO7;
        }

        if ($age >= 7 && $age < 12) {
            return AudiobookAgeRange::FROM7TO12;
        }

        if ($age >= 12 && $age < 16) {
            return AudiobookAgeRange::FROM12TO16;
        }

        if ($age >= 16 && $age < 18) {
            return AudiobookAgeRange::FROM16TO18;
        }

        if ($age >= 18) {
            return AudiobookAgeRange::ABOVE18;
        }

        return AudiobookAgeRange::FROM3TO7;
    }
}