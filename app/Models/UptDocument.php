<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UptDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'upt_location_id',
        'document_type',
        'document_number',
        'file_name',
        'file_path',
        'file_size_kb',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size_kb' => 'integer',
        ];
    }

    public function uptLocation(): BelongsTo
    {
        return $this->belongsTo(UptLocation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
