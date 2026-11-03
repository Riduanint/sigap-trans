<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkpArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'regency_id',
        'kawasan_name',
        'skp_name',
        'unit_code',
        'luas_ha',
        'color',
        'keterangan',
        'sumber_data',
        'centroid_lat',
        'centroid_lng',
        'polygon_geojson',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'luas_ha' => 'float',
            'centroid_lat' => 'float',
            'centroid_lng' => 'float',
            'polygon_geojson' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function villages(): HasMany
    {
        return $this->hasMany(SkpVillage::class);
    }

    public function uptLocations(): HasMany
    {
        return $this->hasMany(UptLocation::class, 'skp_id');
    }

    /**
     * Hitung total luas desa (Km2)
     */
    public function getTotalVillageAreaKm2Attribute(): float
    {
        return (float) $this->villages->sum('area_km2');
    }

    /**
     * Hitung total penduduk desa
     */
    public function getTotalPopulationAttribute(): int
    {
        return (int) $this->villages->sum('population_total');
    }

    /**
     * Hitung total kepala keluarga (KK)
     */
    public function getTotalFamilyCountAttribute(): int
    {
        return (int) $this->villages->sum('family_count');
    }

    /**
     * Konversi ke GeoJSON Feature format Leaflet
     */
    public function toGeoJsonFeature(): array
    {
        $geometry = null;
        if (!empty($this->polygon_geojson)) {
            $geometry = $this->polygon_geojson['geometry'] ?? $this->polygon_geojson;
        }

        return [
            'type' => 'Feature',
            'id' => $this->id,
            'properties' => [
                'id' => $this->id,
                'nama' => $this->skp_name,
                'unit_code' => $this->unit_code,
                'kawasan' => $this->kawasan_name,
                'kabupaten' => $this->regency?->name,
                'regency_id' => $this->regency_id,
                'luas_ha' => (float) $this->luas_ha,
                'luas_formatted' => number_format((float) $this->luas_ha, 2, ',', '.'),
                'keterangan' => $this->keterangan,
                'color' => $this->color ?: '#163A63',
                'sumber' => $this->sumber_data,
                'centroid' => [$this->centroid_lng, $this->centroid_lat],
                'villages_count' => $this->villages->count(),
                'total_population' => $this->total_population,
                'total_family_count' => $this->total_family_count,
            ],
            'geometry' => $geometry,
        ];
    }
}
