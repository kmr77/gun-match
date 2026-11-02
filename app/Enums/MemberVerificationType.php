<?php

namespace App\Enums;

enum MemberVerificationType: string
{
    case Identity = 'identity';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Identity => '本人確認',
            self::Business => '事業者確認',
        };
    }
}
