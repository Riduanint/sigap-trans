<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Regency;
use App\Models\UptLocation;

$regencyCodeMap = [
    'Tanah Laut' => '63.01',
    'Kota Baru' => '63.02',
    'Banjar' => '63.03',
    'Tapin' => '63.05',
    'Hulu Sungai Selatan' => '63.06',
    'Hulu Sungai Tengah' => '63.07',
    'Hulu Sungai Utara' => '63.08',
    'Tabalong' => '63.09',
    'Tanah Bumbu' => '63.10',
    'Balangan' => '63.11',
];

function cleanName($s) {
    if (!$s) return '';
    $s = strtolower($s);
    $s = preg_replace('/^(desa|ds\s*\.?|kelurahan|kel\s*\.?)\s+/i', '', $s);
    $s = preg_replace('/\s+definitif.*$/i', '', $s);
    $s = preg_replace('/\[.*\]/', '', $s);
    $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

function convertPathToGeoJson($pathJson) {
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

$regencies = Regency::with(['uptLocations' => function($q) {
    $q->orderBy('id');
}])->where('id', '!=', 6)->orderBy('id')->get();

$allVillagesByRegency = [];
$cacheDir = __DIR__ . '/cache_boundaries';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// Download or load cached boundary files
foreach ($regencyCodeMap as $name => $code) {
    $cacheFile = "$cacheDir/wilayah_boundaries_kel_{$code}.sql";
    if (!file_exists($cacheFile)) {
        $url = "https://raw.githubusercontent.com/cahyadsn/wilayah_boundaries/master/db/kel/63/wilayah_boundaries_kel_{$code}.sql";
        $opts = [
            'http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 20]
        ];
        $context = stream_context_create($opts);
        $content = @file_get_contents($url, false, $context);
        if ($content) {
            file_put_contents($cacheFile, $content);
        }
    } else {
        $content = file_get_contents($cacheFile);
    }

    if (!$content) {
        echo "Failed to load boundaries for $name ($code)\n";
        continue;
    }

    $pattern = "/\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)/";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
    $villages = [];
    foreach ($matches as $m) {
        $kode = $m[1];
        $nama = $m[2];
        $lat = (float)$m[3];
        $lng = (float)$m[4];
        $pathJson = json_decode($m[5], true);
        $geom = convertPathToGeoJson($pathJson);
        if ($geom) {
            $villages[] = [
                'kode' => $kode,
                'nama' => $nama,
                'clean_nama' => cleanName($nama),
                'centroid' => [round($lng, 6), round($lat, 6)],
                'geometry' => $geom
            ];
        }
    }
    $allVillagesByRegency[$name] = $villages;
    echo "Loaded " . count($villages) . " villages for $name ($code)\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "TEST MATCHING 104 UPTs\n";
echo str_repeat('=', 80) . "\n";

$matchedCount = 0;
$unmatchedList = [];

foreach ($regencies as $reg) {
    $vList = $allVillagesByRegency[$reg->name] ?? [];
    echo "\n--- Regency: {$reg->name} (Total UPTs: " . count($reg->uptLocations) . ") ---\n";

    foreach ($reg->uptLocations as $upt) {
        $rawV = $upt->current_village_name ?? '';
        $rawU = $upt->upt_name;
        $cV = cleanName($rawV);
        $cU = cleanName($rawU);

        $best = null;

        // Pass 1: exact clean match on village
        if ($cV) {
            foreach ($vList as $v) {
                if ($v['clean_nama'] === $cV) {
                    $best = $v;
                    break;
                }
            }
        }

        // Pass 2: split by comma / slash / "dan" in village name
        if (!$best && $cV) {
            $parts = preg_split('/[,\\/]|(\bdan\b)/', $rawV);
            foreach ($parts as $p) {
                $cp = cleanName($p);
                if (!$cp || strlen($cp) < 3) continue;
                foreach ($vList as $v) {
                    if ($v['clean_nama'] === $cp || str_contains($v['clean_nama'], $cp) || str_contains($cp, $v['clean_nama'])) {
                        $best = $v;
                        break 2;
                    }
                }
            }
        }

        // Pass 3: match against UPT name
        if (!$best) {
            $partsU = preg_split('/[,\\/]|(\bsp\b|\bsp\.\b|\bunit\b|\bpir\b|\bpirsus\b)/i', $rawU);
            foreach ($partsU as $p) {
                $cp = cleanName($p);
                if (!$cp || strlen($cp) < 4) continue;
                foreach ($vList as $v) {
                    if ($v['clean_nama'] === $cp || str_contains($v['clean_nama'], $cp) || str_contains($cp, $v['clean_nama'])) {
                        $best = $v;
                        break 2;
                    }
                }
            }
        }

        // Pass 4: check across all Kalsel villages if regency historical boundary changed (e.g. HSU -> Balangan, Kotabaru -> Tanbu)
        if (!$best) {
            foreach ($allVillagesByRegency as $otherRegName => $otherVList) {
                if ($otherRegName === $reg->name) continue;
                foreach ($otherVList as $v) {
                    if ($cV && ($v['clean_nama'] === $cV || (strlen($cV) > 3 && str_contains($v['clean_nama'], $cV)))) {
                        $best = $v;
                        $best['cross_regency'] = $otherRegName;
                        break 2;
                    }
                }
            }
        }

        if ($best) {
            $matchedCount++;
            $extra = isset($best['cross_regency']) ? " [CROSS: {$best['cross_regency']}]" : "";
            echo sprintf("  [OK] UPT #%03d (ID: %3d) '%s' | Desa: '%s' => '%s' (%s)%s\n",
                $upt->upt_number, $upt->id, substr($rawU, 0, 24), substr($rawV, 0, 20), $best['nama'], $best['kode'], $extra);
        } else {
            $unmatchedList[] = $upt;
            echo sprintf("  [MISS] UPT #%03d (ID: %3d) '%s' | Desa: '%s'\n",
                $upt->upt_number, $upt->id, $rawU, $rawV);
        }
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "RESULT: Matched $matchedCount / " . (124 - 20) . " UPTs\n";
echo "Unmatched: " . count($unmatchedList) . "\n";
echo str_repeat('=', 80) . "\n";
