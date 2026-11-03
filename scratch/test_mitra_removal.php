<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "--- 1. Testing DB Check Constraint for role = 'mitra_bpn' ---\n";
try {
    DB::table('users')->insert([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Test Mitra',
        'email' => 'mitra.test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'mitra_bpn',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "ERROR: Database allowed inserting mitra_bpn!\n";
} catch (\Throwable $e) {
    echo "OK: Database rejected mitra_bpn as expected! (" . $e->getMessage() . ")\n";
}

echo "\n--- 2. Testing Allowed Roles in DB ('super_admin', 'operator_kabupaten', 'eksekutif') ---\n";
foreach (['super_admin', 'operator_kabupaten', 'eksekutif'] as $role) {
    try {
        $testEmail = "test.{$role}@example.com";
        DB::table('users')->where('email', $testEmail)->delete();
        DB::table('users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => "Test {$role}",
            'email' => $testEmail,
            'password' => bcrypt('password123'),
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "OK: Role '{$role}' successfully inserted.\n";
        DB::table('users')->where('email', $testEmail)->delete();
    } catch (\Throwable $e) {
        echo "ERROR for role '{$role}': " . $e->getMessage() . "\n";
    }
}

echo "\n--- 3. Testing UserController Validation Rules ---\n";
$validatorMitra = validator(['role' => 'mitra_bpn'], ['role' => 'required|in:super_admin,operator_kabupaten,eksekutif']);
echo "Validation for 'mitra_bpn' fails: " . ($validatorMitra->fails() ? "YES (Rule correctly rejects mitra_bpn)" : "NO (ERROR)") . "\n";

$validatorEksekutif = validator(['role' => 'eksekutif'], ['role' => 'required|in:super_admin,operator_kabupaten,eksekutif']);
echo "Validation for 'eksekutif' passes: " . ($validatorEksekutif->passes() ? "YES (Eksekutif role is preserved)" : "NO (ERROR)") . "\n";

$validatorOperator = validator(['role' => 'operator_kabupaten'], ['role' => 'required|in:super_admin,operator_kabupaten,eksekutif']);
echo "Validation for 'operator_kabupaten' passes: " . ($validatorOperator->passes() ? "YES" : "NO (ERROR)") . "\n";

$validatorSuperAdmin = validator(['role' => 'super_admin'], ['role' => 'required|in:super_admin,operator_kabupaten,eksekutif']);
echo "Validation for 'super_admin' passes: " . ($validatorSuperAdmin->passes() ? "YES" : "NO (ERROR)") . "\n";

echo "\n--- 4. Current DB User Counts by Role ---\n";
$users = User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get();
foreach ($users as $u) {
    echo "Role: {$u->role} -> Count: {$u->count}\n";
}
