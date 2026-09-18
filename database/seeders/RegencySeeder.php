<?php

namespace Database\Seeders;

use App\Models\Regency;
use Illuminate\Database\Seeder;

class RegencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sesuai Dokumen Perancangan Basis Data Spasial SIGAP-TRANS Halaman 10
     */
    public function run(): void
    {
        $regencies = [
            [
                'id' => 1,
                'code_roman' => 'I',
                'name' => 'Tapin',
                'capital_city' => 'Rantau',
                'latitude' => -2.941205,
                'longitude' => 115.234810,
            ],
            [
                'id' => 2,
                'code_roman' => 'II',
                'name' => 'Hulu Sungai Utara',
                'capital_city' => 'Amuntai',
                'latitude' => -2.433842,
                'longitude' => 115.248611,
            ],
            [
                'id' => 3,
                'code_roman' => 'III',
                'name' => 'Balangan',
                'capital_city' => 'Paringin',
                'latitude' => -2.331215,
                'longitude' => 115.632145,
            ],
            [
                'id' => 4,
                'code_roman' => 'IV',
                'name' => 'Tabalong',
                'capital_city' => 'Tanjung',
                'latitude' => -1.862410,
                'longitude' => 115.518620,
            ],
            [
                'id' => 5,
                'code_roman' => 'V',
                'name' => 'Tanah Laut',
                'capital_city' => 'Pelaihari',
                'latitude' => -3.791520,
                'longitude' => 114.786510,
            ],
            [
                'id' => 6,
                'code_roman' => 'VI',
                'name' => 'Barito Kuala',
                'capital_city' => 'Marabahan',
                'latitude' => -3.123510,
                'longitude' => 114.593215,
            ],
            [
                'id' => 7,
                'code_roman' => 'VII',
                'name' => 'Kota Baru',
                'capital_city' => 'Kotabaru',
                'latitude' => -3.187315,
                'longitude' => 116.035420,
            ],
            [
                'id' => 8,
                'code_roman' => 'VIII',
                'name' => 'Tanah Bumbu',
                'capital_city' => 'Batulicin',
                'latitude' => -3.454210,
                'longitude' => 115.703215,
            ],
            [
                'id' => 9,
                'code_roman' => 'IX',
                'name' => 'Banjar',
                'capital_city' => 'Martapura',
                'latitude' => -3.324110,
                'longitude' => 115.081230,
            ],
        ];

        foreach ($regencies as $data) {
            Regency::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }
    }
}
