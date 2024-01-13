<?php

namespace App\Tools;

use Smsapi\Client\Curl\SmsapiHttpClient;
use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;

class SmsTool
{
    /**
     * @param $data
     * @return bool
     */
    public function sendSms($data): bool
    {
        $phone = $data["phone"];
        $content = $data["content"];

        $sendSmsBag = SendSmsBag::withMessage($phone, $content);

        $sms = (bool)(new SmsapiHttpClient())
            ->smsapiPlService($_ENV["SMS_TOKEN"])
            ->smsFeature()
            ->sendSms($sendSmsBag);
        return $sms;
    }
}
