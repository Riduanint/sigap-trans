<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Regency;
use App\Models\UptLocation;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptFamilyCard;
use App\Models\UptDocument;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

echo "1. UPT Domain Test:\n";
$uptCount = UptLocation::count();
$handoverKk = UptLocation::sum('handover_kk');
echo "   Total UPT: $uptCount, Handover KK: $handoverKk\n";

echo "2. SKP Domain Test:\n";
$skpCount = SkpArea::count();
$skpVillages = SkpVillage::count();
$totalAreaHa = SkpArea::sum('luas_ha');
echo "   Total SKP: $skpCount, Villages: $skpVillages, Area Ha: $totalAreaHa\n";

echo "3. Kependudukan Domain Test:\n";
$placementKk = UptLocation::sum('placement_kk');
$placementPop = UptLocation::sum('placement_population');
$avgPopPerKk = $placementKk > 0 ? round($placementPop / $placementKk, 1) : 0;
echo "   Placement KK: $placementKk, Pop: $placementPop, Avg/KK: $avgPopPerKk\n";

echo "4. Pertanahan Domain Test:\n";
$cleanCount = UptLocation::where('issue_status', 'clean')->count();
$warningCount = UptLocation::where('issue_status', 'warning')->count();
$criticalCount = UptLocation::where('issue_status', 'critical')->count();
$shm100 = UptLocation::where('shm_status', 'LIKE', '%100%')->count();
echo "   Clean: $cleanCount, Warning: $warningCount, Critical: $criticalCount, SHM 100%: $shm100\n";

echo "5. Dokumen Domain Test:\n";
$docCount = UptDocument::count();
$docByTypes = UptDocument::select('document_type', DB::raw('count(*) as count'))->groupBy('document_type')->pluck('count', 'document_type');
echo "   Total Docs: $docCount, Types: " . json_encode($docByTypes) . "\n";

echo "6. Audit Log Domain Test:\n";
$logCount = AuditLog::count();
$actions = AuditLog::select('action', DB::raw('count(*) as count'))->groupBy('action')->orderByDesc('count')->limit(5)->pluck('count', 'action');
echo "   Total Logs: $logCount, Top Actions: " . json_encode($actions) . "\n";

