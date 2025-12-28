<?php

declare(strict_types = 1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class SmsService
{
    public function __construct(
        #[Autowire(env: 'SMS_KEY')] private readonly string $smsKey,
        #[Autowire(env: 'SMS_SECRET')] private readonly string $smsSecret,
        #[Autowire(env: 'bool:SEND_EMAIL')] private readonly bool $sendEmail,
    ) {}

    public function sendSms(string $phone, string $content): bool
    {
        if (true === $this->sendEmail) {
            $basic = new Basic($this->smsKey, $this->smsSecret);
            $client = new Client($basic);

            $response = $client->sms()->send(
                new SMS($phone, 'Audiobooks', $content),
            );

            return $response->current()->getStatus() === 0;
        }

        return true;
    }
}
