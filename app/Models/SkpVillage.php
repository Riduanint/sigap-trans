<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkpVillage extends Model
{
    use HasFactory;

    protected $fillable = [
        'skp_area_id',
        'upt_location_id',
        'district_name',
        'village_name',
        'area_km2',
        'population_male',
        'population_female',
        'population_total',
        'family_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'area_km2' => 'float',
            'population_male' => 'integer',
            'population_female' => 'integer',
            'population_total' => 'integer',
            'family_count' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($village) {
            // Pastikan total penduduk selalu terhitung konsisten L + P
            $village->population_total = (int)$village->population_male + (int)$village->population_female;
        });
    }

    public function skpArea(): BelongsTo
    {
        return $this->belongsTo(SkpArea::class);
    }

    public function uptLocation(): BelongsTo
    {
        return $this->belongsTo(UptLocation::class);
    }
}
