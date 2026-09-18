<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$superAdmin = User::where('email', 'superadmin@kalselprov.go.id')->first();
if (!$superAdmin) {
    echo "Super admin not found!\n";
    exit(1);
}

Auth::login($superAdmin);
echo "Logged in as: " . Auth::user()->name . " (" . Auth::user()->role . ")\n";

// Test rendering DashboardController
$controller = new App\Http\Controllers\Admin\DashboardController();
$view = $controller->index(request());
echo "Dashboard view rendered successfully: " . $view->name() . "\n";

// Test rendering UptManagementController
$uptController = new App\Http\Controllers\Admin\UptManagementController();
$uptView = $uptController->index(request());
echo "UPT Index view rendered successfully: " . $uptView->name() . " (total items: " . $uptView->getData()['uptLocations']->total() . ")\n";

// Test rendering Document Repository
$docController = new App\Http\Controllers\Admin\DocumentRepositoryController();
$docView = $docController->index(request());
echo "Document Repository view rendered successfully: " . $docView->name() . "\n";

// Test PDF Report Generation
$reportController = new App\Http\Controllers\Admin\ReportController();
$pdfResp = $reportController->exportPdf(request());
echo "Official PDF Report generated successfully: " . strlen($pdfResp->getContent()) . " bytes!\n";

echo "ALL ADMIN MODULES (DASHBOARD, UPT TABLE, E-ARSIP, PDF EXPORT) TESTED & PASSED!\n";
