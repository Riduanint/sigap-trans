<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\CoordinateParser;

echo "=== TEST 1: DMS with parentheses ===" . PHP_EOL;
$parsed = CoordinateParser::parse('(2°25\'59.9"S 115°40\'49.2"E)');
print_r($parsed);
if (!$parsed || abs($parsed['latitude'] - (-2.433306)) > 0.001) {
    throw new Exception("Test 1 failed!");
}
if (!CoordinateParser::isWithinKalsel($parsed['latitude'], $parsed['longitude'])) {
    throw new Exception("Test 1 Kalsel bounds check failed!");
}
echo "✓ Test 1 Passed!" . PHP_EOL;

echo PHP_EOL . "=== TEST 2: Decimal with parentheses ===" . PHP_EOL;
$parsedDec = CoordinateParser::parse('(-2.433306, 115.680333)');
print_r($parsedDec);
if (!$parsedDec || abs($parsedDec['latitude'] - (-2.433306)) > 0.001) {
    throw new Exception("Test 2 failed!");
}
echo "✓ Test 2 Passed!" . PHP_EOL;

echo PHP_EOL . "=== TEST 3: Out of Kalsel bounds ===" . PHP_EOL;
$out = CoordinateParser::parse('6°12\'00.0"S 106°49\'00.0"E'); // Jakarta
print_r($out);
if (!$out || CoordinateParser::isWithinKalsel($out['latitude'], $out['longitude'])) {
    throw new Exception("Test 3 Jakarta bounds failed!");
}
echo "✓ Jakarta correctly identified as OUTSIDE Kalsel!" . PHP_EOL;

echo PHP_EOL . "=== TEST 4: Round-trip DMS conversion ===" . PHP_EOL;
$dms = CoordinateParser::toDms(-2.433306, 115.680333);
echo "toDms output: " . $dms . PHP_EOL;
$reparsed = CoordinateParser::parse($dms);
if (!$reparsed || abs($reparsed['latitude'] - (-2.433306)) > 0.001) {
    throw new Exception("Test 4 roundtrip failed!");
}
echo "✓ Test 4 Passed!" . PHP_EOL;

echo PHP_EOL . "ALL AUTOMATED TESTS PASSED (100%)!" . PHP_EOL;
