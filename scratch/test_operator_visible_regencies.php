<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Regency;
use App\Models\UptLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Operator\OperatorUptController;
use App\Http\Controllers\Operator\OperatorDashboardController;
use App\Http\Controllers\Operator\OperatorChangeRequestController;

$operator = User::where('role', 'operator_kabupaten')->first();
if (!$operator) {
    throw new Exception("Operator user not found!");
}
auth()->login($operator);

echo "=== TEST 1: OperatorUptController@index visible regencies & UPTs ===" . PHP_EOL;
$uptController = new OperatorUptController();
$req = Request::create(route('operator.upt.index'), 'GET');
$req->setUserResolver(fn() => $operator);

$view = $uptController->index($req);
$regencies = $view->getData()['regencies'];
$uptLocations = $view->getData()['uptLocations'];
$stats = $view->getData()['stats'];

echo "Visible regencies count: " . $regencies->count() . PHP_EOL;
echo "Regencies: " . $regencies->pluck('name')->implode(', ') . PHP_EOL;
echo "Total UPTs in pagination: " . $uptLocations->total() . PHP_EOL;
echo "Stats total UPT: " . $stats['total_upt'] . PHP_EOL;

if ($regencies->count() !== 3) {
    throw new Exception("Expected 3 visible regencies, got {$regencies->count()}!");
}
if ($uptLocations->total() !== 25) {
    throw new Exception("Expected 25 visible UPTs, got {$uptLocations->total()}!");
}
if ($stats['total_upt'] !== 25) {
    throw new Exception("Expected stats total 25, got {$stats['total_upt']}!");
}

// Pastikan tidak ada UPT dari Tapin (id=1), Tanah Laut (id=5), Banjar (id=9), dll.
$invalidUpt = $uptLocations->first(fn($u) => in_array($u->regency_id, [1, 2, 4, 5, 7, 9]));
if ($invalidUpt) {
    throw new Exception("Found UPT from hidden regency: {$invalidUpt->upt_name} (Regency ID: {$invalidUpt->regency_id})");
}
echo "✓ Test 1 Passed: Only visible regencies (Balangan, Barito Kuala, Tanah Bumbu) and their 25 UPTs are returned." . PHP_EOL;

echo PHP_EOL . "=== TEST 2: URL tampering with hidden regency_id (e.g. regency_id=1 Tapin) ===" . PHP_EOL;
$tamperedReq = Request::create(route('operator.upt.index', ['regency_id' => 1]), 'GET');
$tamperedReq->setUserResolver(fn() => $operator);

$viewTampered = $uptController->index($tamperedReq);
$tamperedSelectedId = $viewTampered->getData()['selectedRegencyId'];
$tamperedUptTotal = $viewTampered->getData()['uptLocations']->total();

echo "Selected Regency ID on tampering: {$tamperedSelectedId}" . PHP_EOL;
echo "Total UPTs returned: {$tamperedUptTotal}" . PHP_EOL;

if ($tamperedSelectedId !== 'all') {
    throw new Exception("Tampered hidden regency_id was not sanitized to 'all'!");
}
if ($tamperedUptTotal !== 25) {
    throw new Exception("Tampered query returned wrong UPT count: {$tamperedUptTotal}!");
}
echo "✓ Test 2 Passed: Hidden regency parameter is safely ignored and sanitized." . PHP_EOL;

echo PHP_EOL . "=== TEST 3: OperatorDashboardController@index ===" . PHP_EOL;
$dashController = new OperatorDashboardController();
$dashReq = Request::create(route('operator.dashboard'), 'GET');
$dashReq->setUserResolver(fn() => $operator);

$dashView = $dashController->index($dashReq);
$dashRegencies = $dashView->getData()['regencies'];
$dashTotalUpt = $dashView->getData()['totalUpt'];
$dashUptLocations = $dashView->getData()['uptLocations'];

echo "Dashboard regencies count: " . $dashRegencies->count() . PHP_EOL;
echo "Dashboard total UPT count: " . $dashTotalUpt . PHP_EOL;

if ($dashRegencies->count() !== 3) {
    throw new Exception("Dashboard expected 3 regencies, got {$dashRegencies->count()}!");
}
if ($dashTotalUpt !== 25) {
    throw new Exception("Dashboard expected total 25 UPTs, got {$dashTotalUpt}!");
}
echo "✓ Test 3 Passed: Dashboard only aggregates visible regencies." . PHP_EOL;

echo PHP_EOL . "=== TEST 4: OperatorChangeRequestController@create ===" . PHP_EOL;
$reqController = new OperatorChangeRequestController();
$createReq = Request::create(route('operator.requests.create'), 'GET');
$createReq->setUserResolver(fn() => $operator);

$createView = $reqController->create($createReq);
$createRegencies = $createView->getData()['regencies'];
$createUptLocations = $createView->getData()['uptLocations'];

echo "Create form regencies count: " . $createRegencies->count() . PHP_EOL;
echo "Create form UPT count: " . $createUptLocations->count() . PHP_EOL;

if ($createRegencies->count() !== 3) {
    throw new Exception("Create form expected 3 regencies, got {$createRegencies->count()}!");
}
if ($createUptLocations->count() !== 25) {
    throw new Exception("Create form expected 25 UPTs, got {$createUptLocations->count()}!");
}
echo "✓ Test 4 Passed: Usulan creation dropdowns only contain visible regencies and UPTs." . PHP_EOL;

echo PHP_EOL . "=== TEST 5: Dynamic visibility toggle check ===" . PHP_EOL;
// Temporarily toggle Tapin (id=1) to is_visible = true
Regency::where('id', 1)->update(['is_visible' => true]);

$dynamicView = $uptController->index($req);
$dynRegCount = $dynamicView->getData()['regencies']->count();
$dynUptCount = $dynamicView->getData()['uptLocations']->total();

echo "After enabling Tapin -> Regencies: {$dynRegCount}, Total UPT: {$dynUptCount}" . PHP_EOL;

// Revert Tapin back to false
Regency::where('id', 1)->update(['is_visible' => false]);

$revertView = $uptController->index($req);
$revRegCount = $revertView->getData()['regencies']->count();
$revUptCount = $revertView->getData()['uptLocations']->total();

echo "After reverting Tapin -> Regencies: {$revRegCount}, Total UPT: {$revUptCount}" . PHP_EOL;

if ($dynRegCount !== 4 || $revRegCount !== 3) {
    throw new Exception("Dynamic toggle test failed!");
}
echo "✓ Test 5 Passed: Operator interface dynamically adapts when Super Admin toggles visibility!" . PHP_EOL;

echo PHP_EOL . "ALL 5 VERIFICATION TESTS PASSED SUCCESSFULLY (100%)!" . PHP_EOL;
