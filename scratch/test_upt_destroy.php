<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Regency;
use App\Models\UptLocation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\UptManagementController;

// 1. Ambil super admin
$superAdmin = User::where('role', 'super_admin')->first();
if (!$superAdmin) {
    throw new Exception("Super admin user not found!");
}
auth()->login($superAdmin);

// 2. Buat dummy UPT
$regency = Regency::first();
$dummyNumber = (UptLocation::max('upt_number') ?? 0) + 999;

$testUpt = UptLocation::create([
    'regency_id' => $regency->id,
    'upt_number' => $dummyNumber,
    'upt_name' => 'UPT Test Delete ' . time(),
    'current_village_name' => 'Desa Percobaan',
    'business_pattern' => 'TPLK',
    'placement_year' => '2024',
    'placement_kk' => 10,
    'placement_population' => 40,
    'issue_status' => 'clean',
    'is_verified' => true,
]);

echo "Created Dummy UPT ID: {$testUpt->id}, Number: {$testUpt->upt_number}" . PHP_EOL;

// 3. Panggil destroy melalui Controller
$controller = new UptManagementController();
$request = Request::create(route('admin.upt.destroy', $testUpt->id), 'DELETE');
$request->setUserResolver(fn() => $superAdmin);

$response = $controller->destroy($request, $testUpt->id);

echo "Response status: " . $response->getStatusCode() . PHP_EOL;
echo "Redirect URL: " . $response->getTargetUrl() . PHP_EOL;
echo "Flash message: " . session('success') . PHP_EOL;

// 4. Verifikasi di DB
$exists = UptLocation::find($testUpt->id);
if ($exists) {
    throw new Exception("UPT was NOT deleted!");
}
echo "✓ UPT successfully removed from database!" . PHP_EOL;

// 5. Verifikasi Audit Log
$audit = AuditLog::where('target_table', 'upt_locations')
    ->where('action', 'DELETE_UPT_LOCATION')
    ->where('target_id', (string)$testUpt->id)
    ->first();

if (!$audit) {
    throw new Exception("Audit log for DELETE_UPT_LOCATION was NOT created!");
}
echo "✓ Audit log successfully recorded: " . json_encode($audit->details) . PHP_EOL;

echo "ALL DESTROY TESTS PASSED (100%)!" . PHP_EOL;
