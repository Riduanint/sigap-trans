<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UptChangeRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ViewErrorBag;

ViewFacade::share('errors', new ViewErrorBag);

$superAdmin = User::where('role', 'super_admin')->first();
Auth::login($superAdmin);

DB::beginTransaction();
try {
    // Buat dummy usulan CREATE_UPT yang pending
    $dummy = UptChangeRequest::create([
        'upt_location_id' => null,
        'user_id' => $superAdmin->id,
        'request_type' => 'CREATE_UPT',
        'status' => 'pending',
        'proposed_payload' => [
            'upt_name' => 'UPT Tes Error Check',
            'regency_id' => 1,
            'current_village_name' => 'Desa Tes',
            'submission_note' => 'Cek render dashboard'
        ]
    ]);

    echo "Dummy pending CREATE_UPT created (ID: {$dummy->id})...\n";

    // Panggil controller dan render view lengkapnya ke string HTML
    $controller = new App\Http\Controllers\Admin\DashboardController();
    $view = $controller->index(request());
    $html = $view->render();

    echo "SUCCESS: Admin Dashboard HTML rendered completely! (" . strlen($html) . " bytes)\n";
    if (str_contains($html, 'UPT Tes Error Check')) {
        echo "Found dummy UPT name inside rendered HTML output!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
} finally {
    DB::rollBack();
    echo "Rolled back successfully.\n";
}
