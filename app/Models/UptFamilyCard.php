<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UptFamilyCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'upt_location_id',
        'stage',
        'family_card_number',
        'nik',
        'head_of_family_name',
        'family_members_count',
        'transmigrant_type',
        'origin_province',
        'origin_regency',
        'housing_block',
        'land_certificate_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'family_members_count' => 'integer',
        ];
    }

    /**
     * Relasi ke Lokasi UPT
     */
    public function uptLocation(): BelongsTo
    {
        return $this->belongsTo(UptLocation::class);
    }

    /**
     * Scope untuk Tahap Penempatan Awal
     */
    public function scopePlacement(Builder $query): Builder
    {
        return $query->where('stage', 'placement');
    }

    /**
     * Scope untuk Tahap Serah Terima Pemda
     */
    public function scopeHandover(Builder $query): Builder
    {
        return $query->where('stage', 'handover');
    }
}
