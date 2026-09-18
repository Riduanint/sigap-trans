<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UptChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'upt_location_id',
        'user_id',
        'request_type',
        'proposed_payload',
        'status',
        'reviewer_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'proposed_payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function uptLocation(): BelongsTo
    {
        return $this->belongsTo(UptLocation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
