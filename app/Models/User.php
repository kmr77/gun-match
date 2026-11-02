<?php

namespace App\Models;

use App\Enums\MemberType;
use App\Enums\MemberVerificationStatus;
use App\Enums\MemberVerificationType;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'member_type'])]
#[Hidden([
    'password',
    'remember_token',
    'is_admin',
    'is_active',
    'verification_status',
    'verification_type',
    'verification_method',
    'verified_at',
    'reviewed_by_user_id',
    'reviewed_at',
    'verification_note',
])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'member_type' => MemberType::class,
            'verification_status' => MemberVerificationStatus::class,
            'verification_type' => MemberVerificationType::class,
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'verified_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reviewed_by_user_id');
    }
}
