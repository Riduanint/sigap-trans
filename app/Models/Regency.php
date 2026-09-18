<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_roman',
        'name',
        'capital_city',
        'is_visible',
        'map_color',
        'boundary_polygon',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'boundary_polygon' => 'array',
        ];
    }

    public function uptLocations(): HasMany
    {
        return $this->hasMany(UptLocation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
