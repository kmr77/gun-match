<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['phone_hash', 'code_hash'])]
class SmsVerificationChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'phone_hash',
        'code_hash',
        'expires_at',
        'last_sent_at',
        'window_started_at',
        'attempts',
        'sends_in_window',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'window_started_at' => 'datetime',
            'attempts' => 'integer',
            'sends_in_window' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
