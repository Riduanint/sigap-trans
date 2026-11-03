<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Support\CoordinateParser;

$testCases = [
    // 1. User sample
    "2°25'59.9\"S 115°40'49.2\"E" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 2. User sample with comma
    "2°25'59.9\"S, 115°40'49.2\"E" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 3. User sample with spaces
    "2° 25' 59.9\" S 115° 40' 49.2\" E" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 4. Decimal format from Google Maps
    "-2.433306, 115.680333" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 5. Decimal format with space only
    "-2.433306 115.680333" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 6. Decimal with cardinal letters
    "2.433306S 115.680333E" => ['lat' => -2.433306, 'lng' => 115.680333],
    
    // 7. Balangan sample (Batu Mandi)
    "-2.294058, 115.58754" => ['lat' => -2.294058, 'lng' => 115.58754],
    
    // 8. DMS without special symbols (space separated)
    "2 25 59.9 S 115 40 49.2 E" => ['lat' => -2.433306, 'lng' => 115.680333],
];

echo "=== TESTING COORDINATE PARSER ===\n\n";

$allPassed = true;
foreach ($testCases as $input => $expected) {
    $result = CoordinateParser::parse($input);
    if (!$result) {
        echo "❌ FAILED to parse: '$input'\n";
        $allPassed = false;
        continue;
    }

    $latDiff = abs($result['latitude'] - $expected['lat']);
    $lngDiff = abs($result['longitude'] - $expected['lng']);

    if ($latDiff > 0.0001 || $lngDiff > 0.0001) {
        echo "❌ MISMATCH: '$input'\n";
        echo "   Result:   Lat {$result['latitude']}, Lng {$result['longitude']}\n";
        echo "   Expected: Lat {$expected['lat']}, Lng {$expected['lng']}\n";
        $allPassed = false;
    } else {
        echo "✅ OK: '$input'\n";
        echo "   -> Lat: {$result['latitude']}, Lng: {$result['longitude']}\n";
        echo "   -> Formatted DMS: {$result['dms']}\n";
        echo "   -> Is in Kalsel: " . (CoordinateParser::isWithinKalsel($result['latitude'], $result['longitude']) ? "YES" : "NO") . "\n\n";
    }
}

// Test reverse conversion toDms
$dmsRoundtrip = CoordinateParser::toDms(-2.433306, 115.680333);
echo "Roundtrip DMS for -2.433306, 115.680333: {$dmsRoundtrip}\n";

if ($allPassed) {
    echo "\n🎉 ALL TESTS PASSED SUCCESSFULLY (100%)!\n";
} else {
    echo "\n⚠️ SOME TESTS FAILED!\n";
    exit(1);
}
