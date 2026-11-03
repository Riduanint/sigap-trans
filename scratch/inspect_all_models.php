<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptFamilyCard;
use App\Models\UptDocument;
use App\Models\AuditLog;
use App\Models\UptLocation;
use Illuminate\Support\Facades\Schema;

echo "=== SKP VILLAGES ===\n";
echo "Columns: " . implode(', ', Schema::getColumnListing('skp_villages')) . "\n";
echo "Count: " . SkpVillage::count() . "\n";

echo "\n=== UPT DOCUMENTS ===\n";
$docTypes = UptDocument::select('document_type')->distinct()->pluck('document_type');
echo "Types: " . implode(', ', $docTypes->toArray()) . " | Total Docs: " . UptDocument::count() . "\n";

echo "\n=== UPT PERTANAHAN / SHM STATUSES ===\n";
$shmStatuses = UptLocation::select('shm_status')->distinct()->pluck('shm_status');
echo "SHM statuses in UPT: " . implode(', ', $shmStatuses->toArray()) . "\n";

echo "\n=== AUDIT LOGS ===\n";
$actions = AuditLog::select('action')->distinct()->pluck('action');
echo "Actions: " . implode(', ', $actions->toArray()) . " | Total Logs: " . AuditLog::count() . "\n";
