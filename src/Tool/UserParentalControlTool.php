<?php

declare(strict_types = 1);

namespace App\Tool;

use App\Entity\User;
use App\Enums\AudiobookAgeRange;
use DateTime;

class UserParentalControlTool
{
    public function getUserAudiobookAgeValue(User $user): ?AudiobookAgeRange
    {
        $birthday = $user->getUserInformation()->getBirthday();

        if (null === $birthday) {
            return null;
        }

        $today = new DateTime();

        $age = $today->diff($birthday)->y;

        if (3 <= $age && 7 > $age) {
            return AudiobookAgeRange::FROM3TO7;
        }

        if (7 <= $age && 12 > $age) {
            return AudiobookAgeRange::FROM7TO12;
        }

        if (12 <= $age && 16 > $age) {
            return AudiobookAgeRange::FROM12TO16;
        }

        if (16 <= $age && 18 > $age) {
            return AudiobookAgeRange::FROM16TO18;
        }

        if (18 <= $age) {
            return AudiobookAgeRange::ABOVE18;
        }

        return AudiobookAgeRange::FROM3TO7;
    }
}
