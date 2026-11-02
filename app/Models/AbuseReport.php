<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbuseReport extends Model
{
    public const TYPE_LABELS = [
        'terms_violation' => '規約違反',
        'fraud_or_misuse' => '不正利用',
        'safety_concern' => '安全上の懸念',
        'other' => 'その他',
    ];

    public const STATUS_LABELS = [
        'pending' => '未対応',
        'in_progress' => '対応中',
        'completed' => '対応済み',
    ];

    protected $fillable = [
        'reporter_user_id',
        'type',
        'target_information',
        'details',
        'status',
        'admin_note',
        'handled_at',
        'handled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
