<?php

declare(strict_types=1);

namespace App\Tool;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class SmsTool
{
    /**
     * @param string $phone
     * @param string $content
     * @return bool
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function sendSms(string $phone, string $content): bool
    {
        if ($_ENV['APP_ENV'] !== 'test') {
            $basic = new Basic($_ENV['SMS_KEY'], $_ENV['SMS_SECRET']);
            $client = new Client($basic);

            $response = $client->sms()->send(
                new SMS($phone, 'Audiobooks', $content)
            );

            return $response->current()->getStatus() === 0;
        }

        return true;
    }
}
