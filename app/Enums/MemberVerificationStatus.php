<?php

namespace App\Enums;

enum MemberVerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => '未確認',
            self::Pending => '確認待ち',
            self::Verified => '確認済み',
            self::Returned => '差戻し',
        };
    }
}
