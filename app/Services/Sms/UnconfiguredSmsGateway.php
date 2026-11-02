<?php

namespace App\Services\Sms;

class UnconfiguredSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        throw new SmsDeliveryException('SMS送信事業者が設定されていません。');
    }
}
