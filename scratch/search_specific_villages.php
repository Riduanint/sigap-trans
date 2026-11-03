<?php
$cacheDir = __DIR__ . '/cache_boundaries';
$allFiles = glob("$cacheDir/*.sql");
$allVillages = [];

foreach ($allFiles as $f) {
    $content = file_get_contents($f);
    $pattern = "/\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)/";
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $allVillages[] = [
            'kode' => $m[1],
            'nama' => $m[2],
            'lat' => (float)$m[3],
            'lng' => (float)$m[4],
        ];
    }
}

echo "Total Kemendagri villages in Kalsel: " . count($allVillages) . "\n\n";

$searches = [
    'Sabah' => ['sabah', 'sidodadi'],
    'Rawa Muning' => ['muning', 'papagan', 'rawa'],
    'Buntu Karam' => ['buntu', 'karau', 'karam'],
    'Haruway' => ['haruai', 'haruway', 'riut'],
    'Sei Anyar' => ['anyar', 'sungai anyar'],
    'HTI Sungai Misim' => ['misim', 'kembang kuning', 'muara uya'],
    'Kertabuana' => ['kerta buana', 'kertabuana'],
    'GiriMulya' => ['giri mulya', 'girimulya', 'mustika', 'waringin'],
    'Manunggal' => ['manunggal', 'karang bintang'],
    'Pemulung Ulin' => ['ulin', 'pandan sari', 'winangun'],
    'Mustika Baru' => ['mustika'],
    'Sangsang' => ['sangsang', 'kelumpang'],
    'Batulicin Irigasi' => ['irigasi', 'batulicin'],
    'Trombung Sari' => ['trombung', 'durian', 'rantau jaya'],
    'Arum Sari' => ['arum', 'satui'],
    'Karya Bhakti' => ['karya bhakti', 'karya bakti'],
    'Selimuran' => ['selimuran', 'salimuran'],
    'Pasir Sembaga' => ['sembaga', 'pasir'],
    'Bukit Baru' => ['bukit baru', 'satui', 'kintap'],
    'Sendang Jaya' => ['sendang', 'sindang', 'cintapuri'],
    'Riam Kanan' => ['aranio', 'tiwingan', 'riam'],
    'Cinta Puri' => ['cintapuri', 'cinta puri'],
];

foreach ($searches as $label => $keywords) {
    echo "=== Searching for: $label ===\n";
    $found = 0;
    foreach ($allVillages as $v) {
        $nameLower = strtolower($v['nama']);
        foreach ($keywords as $kw) {
            if (str_contains($nameLower, $kw)) {
                echo "  -> Found: {$v['nama']} ({$v['kode']}) [{$v['lat']}, {$v['lng']}]\n";
                $found++;
                break;
            }
        }
    }
    if ($found === 0) {
        echo "  -> NONE FOUND\n";
    }
}

