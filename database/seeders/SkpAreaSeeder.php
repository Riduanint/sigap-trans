<?php

namespace Database\Seeders;

use App\Models\Regency;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptLocation;
use Illuminate\Database\Seeder;

class SkpAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batola = Regency::where('name', 'ILIKE', '%Barito Kuala%')->first()
            ?? Regency::find(6);

        $regencyId = $batola ? $batola->id : 6;

        // 1. Impor SKP dari GeoJSON Cahaya Baru jika ada
        $geojsonPath = public_path('data/skp_cahaya_baru.geojson');
        if (file_exists($geojsonPath)) {
            $geojson = json_decode(file_get_contents($geojsonPath), true);
            if (!empty($geojson['features'])) {
                foreach ($geojson['features'] as $feature) {
                    $p = $feature['properties'] ?? [];
                    $name = $p['nama'] ?? 'SKP Tanpa Nama';
                    $unitCode = $p['unit_code'] ?? null;
                    $kawasan = $p['kawasan'] ?? 'Kawasan Transmigrasi Cahaya Baru';
                    $luasHa = isset($p['luas_ha']) ? (float)$p['luas_ha'] : null;
                    $color = $p['color'] ?? '#163A63';
                    $keterangan = $p['keterangan'] ?? null;
                    $sumber = $p['sumber'] ?? 'DPMD Kab. Barito Kuala & BIG (2023)';
                    $centroidLng = $p['centroid'][0] ?? null;
                    $centroidLat = $p['centroid'][1] ?? null;

                    SkpArea::updateOrCreate(
                        [
                            'regency_id' => $regencyId,
                            'skp_name' => $name,
                        ],
                        [
                            'kawasan_name' => $kawasan,
                            'unit_code' => $unitCode,
                            'luas_ha' => $luasHa,
                            'color' => $color,
                            'keterangan' => $keterangan,
                            'sumber_data' => $sumber,
                            'centroid_lng' => $centroidLng,
                            'centroid_lat' => $centroidLat,
                            'polygon_geojson' => $feature['geometry'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // 2. Pastikan SKP-B dan SKP-C tersedia (rujukan tabel Mandastana)
        $skpB = SkpArea::where('regency_id', $regencyId)
            ->where(fn($q) => $q->where('skp_name', 'SKP-B')->orWhere('skp_name', 'SKP B')->orWhere('unit_code', 'B'))
            ->first();
        if ($skpB) {
            $skpB->update([
                'skp_name' => 'SKP-B',
                'kawasan_name' => 'Kawasan Transmigrasi Cahaya Baru',
                'unit_code' => 'B',
                'luas_ha' => 12305.00,
                'color' => '#107C41',
                'keterangan' => 'Satuan Kawasan Pengembangan B - Wilayah Mandastana & Sekitarnya',
                'sumber_data' => 'DPMD & BPS Kab. Barito Kuala',
                'is_active' => true,
            ]);
        } else {
            $skpB = SkpArea::create([
                'regency_id' => $regencyId,
                'skp_name' => 'SKP-B',
                'kawasan_name' => 'Kawasan Transmigrasi Cahaya Baru',
                'unit_code' => 'B',
                'luas_ha' => 12305.00,
                'color' => '#107C41',
                'keterangan' => 'Satuan Kawasan Pengembangan B - Wilayah Mandastana & Sekitarnya',
                'sumber_data' => 'DPMD & BPS Kab. Barito Kuala',
                'is_active' => true,
            ]);
        }

        $skpC = SkpArea::where('regency_id', $regencyId)
            ->where(fn($q) => $q->where('skp_name', 'SKP-C')->orWhere('skp_name', 'SKP C')->orWhere('unit_code', 'C'))
            ->first();
        if ($skpC) {
            $skpC->update([
                'skp_name' => 'SKP-C',
                'kawasan_name' => 'Kawasan Transmigrasi Cahaya Baru',
                'unit_code' => 'C',
                'luas_ha' => 1295.00,
                'color' => '#0E2A47',
                'keterangan' => 'Satuan Kawasan Pengembangan C - Wilayah Mandastana (Karang Indah & Karang Bunga)',
                'sumber_data' => 'DPMD & BPS Kab. Barito Kuala',
                'is_active' => true,
            ]);
        } else {
            $skpC = SkpArea::create([
                'regency_id' => $regencyId,
                'skp_name' => 'SKP-C',
                'kawasan_name' => 'Kawasan Transmigrasi Cahaya Baru',
                'unit_code' => 'C',
                'luas_ha' => 1295.00,
                'color' => '#0E2A47',
                'keterangan' => 'Satuan Kawasan Pengembangan C - Wilayah Mandastana (Karang Indah & Karang Bunga)',
                'sumber_data' => 'DPMD & BPS Kab. Barito Kuala',
                'is_active' => true,
            ]);
        }

        // 3. Data 14 Desa Mandastana sesuai tabel resmi rujukan
        $mandastanaVillages = [
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Bangkit Baru',
                'area_km2' => 12.00,
                'population_male' => 388,
                'population_female' => 370,
                'population_total' => 758,
                'family_count' => 244,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Antasan Segera',
                'area_km2' => 10.00,
                'population_male' => 415,
                'population_female' => 429,
                'population_total' => 844,
                'family_count' => 274,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Terantang',
                'area_km2' => 8.00,
                'population_male' => 1015,
                'population_female' => 1059,
                'population_total' => 2074,
                'family_count' => 674,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Tanipah',
                'area_km2' => 6.50,
                'population_male' => 524,
                'population_female' => 518,
                'population_total' => 1060,
                'family_count' => 343,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Puntik Luar',
                'area_km2' => 10.50,
                'population_male' => 919,
                'population_female' => 887,
                'population_total' => 1806,
                'family_count' => 562,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Puntik Dalam',
                'area_km2' => 12.05,
                'population_male' => 640,
                'population_female' => 587,
                'population_total' => 1227,
                'family_count' => 379,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Tabing Rimbah',
                'area_km2' => 18.00,
                'population_male' => 1011,
                'population_female' => 980,
                'population_total' => 1991,
                'family_count' => 600,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Pantai Hambawang',
                'area_km2' => 11.00,
                'population_male' => 379,
                'population_female' => 387,
                'population_total' => 766,
                'family_count' => 268,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Tatah Alayung',
                'area_km2' => 12.00,
                'population_male' => 491,
                'population_female' => 453,
                'population_total' => 944,
                'family_count' => 328,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Puntik Tengah',
                'area_km2' => 10.00,
                'population_male' => 952,
                'population_female' => 936,
                'population_total' => 1888,
                'family_count' => 563,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Lokrawa',
                'area_km2' => 8.00,
                'population_male' => 671,
                'population_female' => 640,
                'population_total' => 1311,
                'family_count' => 409,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Sei Ramania',
                'area_km2' => 5.00,
                'population_male' => 290,
                'population_female' => 286,
                'population_total' => 576,
                'family_count' => 198,
                'skp' => 'SKP-B',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Karang Indah',
                'area_km2' => 4.92,
                'population_male' => 651,
                'population_female' => 654,
                'population_total' => 1305,
                'family_count' => 427,
                'skp' => 'SKP-C',
            ],
            [
                'district_name' => 'Mandastana',
                'village_name' => 'Karang Bunga',
                'area_km2' => 8.03,
                'population_male' => 1060,
                'population_female' => 1023,
                'population_total' => 2083,
                'family_count' => 685,
                'skp' => 'SKP-C',
            ],
        ];

        foreach ($mandastanaVillages as $item) {
            $targetSkp = ($item['skp'] === 'SKP-C') ? $skpC : $skpB;

            // Cari apakah ada UPT terkait di Barito Kuala
            $matchingUpt = UptLocation::where('regency_id', $regencyId)
                ->where(function ($q) use ($item) {
                    $q->where('current_village_name', 'ILIKE', '%' . $item['village_name'] . '%')
                      ->orWhere('upt_name', 'ILIKE', '%' . $item['village_name'] . '%');
                })
                ->first();

            SkpVillage::updateOrCreate(
                [
                    'skp_area_id' => $targetSkp->id,
                    'village_name' => $item['village_name'],
                ],
                [
                    'upt_location_id' => $matchingUpt?->id,
                    'district_name' => $item['district_name'],
                    'area_km2' => $item['area_km2'],
                    'population_male' => $item['population_male'],
                    'population_female' => $item['population_female'],
                    'population_total' => $item['population_total'],
                    'family_count' => $item['family_count'],
                    'notes' => $item['skp'],
                ]
            );

            // Jika UPT cocok, hubungkan skp_id pada UPT tersebut
            if ($matchingUpt && is_null($matchingUpt->skp_id)) {
                $matchingUpt->update(['skp_id' => $targetSkp->id]);
            }
        }
    }
}
