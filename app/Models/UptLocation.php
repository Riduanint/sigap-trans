<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UptLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'regency_id',
        'upt_number',
        'upt_name',
        'current_village_name',
        'business_pattern',
        'placement_year',
        'placement_kk',
        'placement_population',
        'handover_year',
        'handover_kk',
        'handover_population',
        'issue_status',
        'issue_note',
        'coordinate_point',
        'polygon_area',
        'is_verified',
        'latitude',
        'longitude',
        'polygon_geojson',
        'shm_status',
    ];

    protected function casts(): array
    {
        return [
            'upt_number' => 'integer',
            'placement_kk' => 'integer',
            'placement_population' => 'integer',
            'handover_kk' => 'integer',
            'handover_population' => 'integer',
            'is_verified' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'polygon_geojson' => 'array',
        ];
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(UptChangeRequest::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UptDocument::class);
    }

    /**
     * Seluruh Kartu Keluarga Transmigran
     */
    public function familyCards(): HasMany
    {
        return $this->hasMany(UptFamilyCard::class);
    }

    /**
     * Registri Warga Penempatan Awal
     */
    public function placementFamilyCards(): HasMany
    {
        return $this->hasMany(UptFamilyCard::class)->where('stage', 'placement');
    }

    /**
     * Registri Warga Serah Terima Pemda
     */
    public function handoverFamilyCards(): HasMany
    {
        return $this->hasMany(UptFamilyCard::class)->where('stage', 'handover');
    }

    /**
     * Helper untuk konversi ke GeoJSON Feature format Leaflet.js
     */
    public function toGeoJsonFeature(): array
    {
        return [
            'type' => 'Feature',
            'id' => $this->id,
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $this->longitude, (float) $this->latitude],
            ],
            'properties' => [
                'upt_number' => $this->upt_number,
                'upt_name' => $this->upt_name,
                'current_village_name' => $this->current_village_name,
                'regency_name' => $this->regency?->name,
                'business_pattern' => $this->business_pattern,
                'placement_year' => $this->placement_year,
                'placement_kk' => $this->placement_kk,
                'placement_population' => $this->placement_population,
                'handover_year' => $this->handover_year,
                'handover_kk' => $this->handover_kk,
                'handover_population' => $this->handover_population,
                'issue_status' => $this->issue_status,
                'issue_note' => $this->issue_note,
                'is_verified' => $this->is_verified,
                'shm_status' => $this->shm_status,
            ],
        ];
    }
}
