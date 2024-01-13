<?php

namespace App\Tool;

use Smsapi\Client\Curl\SmsapiHttpClient;
use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;

class SmsTool
{
    /**
     * @param string $phone
     * @param string $content
     * @return void
     */
    public function sendSms(string $phone, string $content): void
    {
        if ($_ENV["APP_ENV"] !== "test") {
            $sendSmsBag = SendSmsBag::withMessage($phone, $content);

            (new SmsapiHttpClient())
                ->smsapiPlService($_ENV["SMS_TOKEN"])
                ->smsFeature()
                ->sendSms($sendSmsBag);
        }
    }
}
