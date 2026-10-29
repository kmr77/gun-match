<?php

namespace App\Enums;

enum MemberType: string
{
    case Individual = 'individual';
    case GunShop = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Individual => '個人会員',
            self::GunShop => '銃砲店会員',
        };
    }
}
