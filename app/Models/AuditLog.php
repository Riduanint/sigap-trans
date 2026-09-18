<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'target_table',
        'target_id',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper static untuk mencatat log sistem forensik
     */
    public static function log(string $action, string $targetTable, string|int $targetId, ?array $details = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_table' => $targetTable,
            'target_id' => (string) $targetId,
            'details' => $details,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
