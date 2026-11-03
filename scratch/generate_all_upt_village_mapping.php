<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\Regency;
use App\Models\UptLocation;

$cacheDir = __DIR__ . '/cache_boundaries';
$allFiles = glob("$cacheDir/*.sql");
$allVillagesByCode = [];
$allVillagesByName = [];

function cleanStr($s) {
    if (!$s) return '';
    $s = strtolower($s);
    $s = preg_replace('/^(desa|ds\s*\.?|kelurahan|kel\s*\.?)\s+/i', '', $s);
    $s = preg_replace('/\s+definitif.*$/i', '', $s);
    $s = preg_replace('/\[.*\]/', '', $s);
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

function parseGeoJson($pathJson) {
    if (!is_array($pathJson) || empty($pathJson)) return null;
    $first = $pathJson[0];
    if (!is_array($first) || empty($first)) return null;

    if (isset($first[0]) && is_numeric($first[0])) {
        $ring = [];
        foreach ($pathJson as $pt) {
            $ring[] = [round((float)$pt[1], 6), round((float)$pt[0], 6)];
        }
        return ['type' => 'Polygon', 'coordinates' => [$ring]];
    }

    $second = $first[0];
    if (is_array($second) && isset($second[0]) && is_numeric($second[0])) {
        $rings = [];
        foreach ($pathJson as $r) {
            $ring = [];
            foreach ($r as $pt) {
                if (is_array($pt) && isset($pt[1])) {
                    $ring[] = [round((float)$pt[1], 6), round((float)$pt[0], 6)];
                }
            }
            if (!empty($ring)) $rings[] = $ring;
        }
        return ['type' => 'Polygon', 'coordinates' => $rings];
    }

    $multipoly = [];
    foreach ($pathJson as $poly) {
        $rings = [];
        foreach ($poly as $r) {
            $ring = [];
            foreach ($r as $pt) {
                if (is_array($pt) && isset($pt[1])) {
                    $ring[] = [round((float)$pt[1], 6), round((float)$pt[0], 6)];
                }
            }
            if (!empty($ring)) $rings[] = $ring;
        }
        if (!empty($rings)) $multipoly[] = $rings;
    }
    return ['type' => 'MultiPolygon', 'coordinates' => $multipoly];
}

foreach ($allFiles as $f) {
    $content = file_get_contents($f);
    $pattern = "/\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)/";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $kode = $m[1];
        $nama = $m[2];
        $lat = (float)$m[3];
        $lng = (float)$m[4];
        $pathJson = json_decode($m[5], true);
        $geom = parseGeoJson($pathJson);
        if ($geom) {
            $entry = [
                'kode' => $kode,
                'nama' => $nama,
                'clean_nama' => cleanStr($nama),
                'centroid' => [round($lng, 6), round($lat, 6)],
                'geometry' => $geom
            ];
            $allVillagesByCode[$kode] = $entry;
            $allVillagesByName[cleanStr($nama)] = $entry;
        }
    }
}

echo "Total Kemendagri villages loaded into memory: " . count($allVillagesByCode) . "\n";

// Manual explicit overrides for edge cases (historical name splits, SP codes, typos in old data)
$explicitCodeMapping = [
    // Tapin
    3 => '63.05.09.2001', // UPT Sidodadi (Desa Sabah / Salam Babaris)
    8 => '63.05.03.2017', // UPT Rawa Muning SP 2 -> Papagan Makmur (Candi Laras Selatan)
    9 => '63.05.05.2010', // UPT Rawa Muning SP 3 -> Sungai Rutas Hulu / Margasari
    
    // HSU / Balangan
    11 => '63.11.01.2004', // UPT PIR Paringin II (Buntu Karam -> Buntu Karau)
    13 => '63.11.02.2010', // UPT PIR Paringin IV (Haruway, Gunung Riut -> Gunung Riut)
    16 => '63.11.01.2019', // UPT Batu Mandi (Panimbaan / Batumandi)
    
    // Tabalong
    20 => '63.09.04.2010', // UPT Kambitin II -> Kambitin
    27 => '63.09.05.2002', // UPT HTI Sungai Misim -> Wirang / Haruai
    28 => '63.09.07.2014', // UPT HTI Sungai Kumap -> Sungai Kumap
    
    // Tanah Laut
    36 => '63.01.02.2002', // UPT Jorong Jalur -> Jorong
    42 => '63.01.02.2003', // UPT Jorong Asam-Asam I -> Asam Asam
    43 => '63.01.02.2009', // UPT Jorong Asam-Asam II -> Asam Jaya
    49 => '63.01.09.2014', // UPT Kayu Habang -> Kayu Habang
    
    // Kotabaru & Tanah Bumbu
    74 => '63.10.03.2008', // Sebamban I (Sari Mulya / Sungai Loban)
    76 => '63.10.03.2011', // Sebamban III (Kerta Buana)
    77 => '63.10.07.2007', // Batulicin I (Manunggal / Karang Bintang)
    79 => '63.10.10.2001', // Sebamban VI (Giri Mulya / Kuranji)
    81 => '63.10.07.2005', // Batulicin PIR (Pematang Ulin)
    87 => '63.10.10.2004', // Sebamban VI Lasung (Mustika)
    88 => '63.02.11.2005', // Klumpang AC Sangsang (Kec. Kelumpang Hulu)
    92 => '63.10.07.2006', // Batulicin Irigasi (Batu Licin Irigasi)
    97 => '63.02.15.2009', // Sei Durian (Rantau Jaya / Sungai Durian)
    99 => '63.10.04.2019', // HTI Satui (Sumber Arum / Satui)
    103 => '63.10.11.2012', // Batulicin SP 2 (UPT Karya Bakti)
    107 => '63.10.11.2013', // Setiung Salimuran (Salimuran)
    109 => '63.02.14.2004', // Pasir Sembaga (Kec. Hampang)
    112 => '63.10.10.2001', // Bukit Baru (Giri Mulya / Kuranji)
    
    // Banjar
    116 => '63.03.20.2010', // Galam Rabah SP.3 (Sindang Jaya)
    117 => '63.03.11.2001', // Riam Kanan SP. 1 (Tiwingan Lama / Aranio)
    118 => '63.03.11.2009', // Riam Kanan SP .2 (Tiwingan Baru / Aranio)
    119 => '63.03.10.2015', // Hakim (Hakim Makmur)
    120 => '63.03.20.2002', // Galam Rabah SP.4 (Garis Hanyar / Cintapuri)
    121 => '63.03.20.2004', // Galam Rabah SP.5 (Surian Hanyar / Cintapuri)
    122 => '63.03.20.2005', // Galam Rabah SP.6 / Belanti (Benua Anyar / Cintapuri)
    124 => '63.03.20.2003', // Cinta Puri (Cintapuri)
];

$upts = UptLocation::where('regency_id', '!=', 6)->orderBy('id')->get();
$finalMapping = [];
$matched = 0;
$missing = 0;

foreach ($upts as $upt) {
    $uid = $upt->id;
    $selectedVillage = null;
    $method = '';

    // Check manual override first
    if (isset($explicitCodeMapping[$uid])) {
        $c = $explicitCodeMapping[$uid];
        if (isset($allVillagesByCode[$c])) {
            $selectedVillage = $allVillagesByCode[$c];
            $method = "EXPLICIT_OVERRIDE";
        }
    }

    // Auto heuristics
    if (!$selectedVillage) {
        $cV = cleanStr($upt->current_village_name);
        if ($cV && isset($allVillagesByName[$cV])) {
            $selectedVillage = $allVillagesByName[$cV];
            $method = "EXACT_NAME";
        }
    }

    if (!$selectedVillage && $upt->current_village_name) {
        $parts = preg_split('/[,\\/]|(\bdan\b)/', $upt->current_village_name);
        foreach ($parts as $p) {
            $cp = cleanStr($p);
            if (!$cp || strlen($cp) < 3) continue;
            if (isset($allVillagesByName[$cp])) {
                $selectedVillage = $allVillagesByName[$cp];
                $method = "PARTIAL_VILLAGE_NAME";
                break;
            }
        }
    }

    if (!$selectedVillage && $upt->upt_name) {
        $partsU = preg_split('/[,\\/]|(\bsp\b|\bsp\.\b|\bunit\b|\bpir\b|\bpirsus\b)/i', $upt->upt_name);
        foreach ($partsU as $p) {
            $cp = cleanStr($p);
            if (!$cp || strlen($cp) < 4) continue;
            if (isset($allVillagesByName[$cp])) {
                $selectedVillage = $allVillagesByName[$cp];
                $method = "UPT_NAME_MATCH";
                break;
            }
        }
    }

    if ($selectedVillage) {
        $matched++;
        $finalMapping[$uid] = [
            'upt_id' => $uid,
            'upt_name' => $upt->upt_name,
            'original_village' => $upt->current_village_name,
            'matched_village' => $selectedVillage['nama'],
            'kode_kemendagri' => $selectedVillage['kode'],
            'method' => $method,
            'centroid' => $selectedVillage['centroid'],
            'geometry' => $selectedVillage['geometry']
        ];
        echo sprintf("UPT #%03d (ID: %3d) '%s' => '%s' (%s) [%s]\n",
            $upt->upt_number, $uid, substr($upt->upt_name, 0, 22), $selectedVillage['nama'], $selectedVillage['kode'], $method);
    } else {
        $missing++;
        echo sprintf("FAIL: UPT #%03d (ID: %3d) '%s' | Desa: '%s'\n",
            $upt->upt_number, $uid, $upt->upt_name, $upt->current_village_name);
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "MAPPING SUMMARY:\n";
echo "Total UPTs processed: " . count($upts) . "\n";
echo "Matched: $matched\n";
echo "Missing: $missing\n";
echo str_repeat('=', 80) . "\n";

if ($missing === 0) {
    file_put_contents(__DIR__ . '/all_kabupaten_upt_village_boundaries.json', json_encode($finalMapping, JSON_PRETTY_PRINT));
    echo "Saved all 104 UPT village boundaries to scratch/all_kabupaten_upt_village_boundaries.json\n";
}
