<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsConsent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'terms_version',
        'consented_at',
        'consent_method',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
