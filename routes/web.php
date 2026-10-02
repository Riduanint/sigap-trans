<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentRepositoryController;
use App\Http\Controllers\Admin\FamilyCardManagementController;
use App\Http\Controllers\Admin\HandoverManagementController;
use App\Http\Controllers\Admin\LandCertificateController;
use App\Http\Controllers\Admin\PlacementManagementController;
use App\Http\Controllers\Admin\RegencyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UptManagementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Operator\OperatorChangeRequestController;
use App\Http\Controllers\Operator\OperatorDashboardController;
use App\Http\Controllers\Operator\OperatorUptController;
use App\Http\Controllers\ProfileController;
use App\Models\Regency;
use Illuminate\Support\Facades\Route;

// Beranda: Portal Peta WebGIS Interaktif
Route::get('/', function () {
    $query = Regency::query();
    if (request()->filled('upt_id')) {
        $targetUpt = \App\Models\UptLocation::find(request('upt_id'));
        if ($targetUpt) {
            $query->where(function ($q) use ($targetUpt) {
                $q->where('is_visible', true)->orWhere('id', $targetUpt->regency_id);
            });
        } else {
            $query->where('is_visible', true);
        }
    } else {
        $query->where('is_visible', true);
    }
    $visibleRegencies = $query->withCount('uptLocations')->orderBy('id')->get();
    return view('webgis.index', compact('visibleRegencies'));
})->name('home');

// Redirect Dashboard Berdasarkan Peran (Konsolidasi 2 Peran Utama)
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    return match ($user->role) {
        'operator_kabupaten' => redirect()->route('operator.dashboard'),
        default => redirect()->route('admin.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

// 1. MODUL RUANG KENDALI SUPER ADMIN PROVINSI (TERINTEGRASI EKSEKUTIF & MITRA BPN)
Route::middleware(['auth', 'role:super_admin,eksekutif,mitra_bpn'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.pdf');

    // Analitik Kebijakan & Tren Transmigrasi (Integrasi Eksekutif)
    Route::get('/analitik', [AnalyticsController::class, 'index'])->name('analitik.index');
    Route::get('/analitik/pdf', [AnalyticsController::class, 'exportPdf'])->name('analitik.pdf');

    // Monitoring Sertipikasi Tanah (Integrasi Mitra BPN)
    Route::get('/sertifikat-tanah', [LandCertificateController::class, 'index'])->name('sertifikat-tanah.index');
    Route::match(['put', 'patch'], '/sertifikat-tanah/{id}', [LandCertificateController::class, 'update'])->name('sertifikat-tanah.update')->whereNumber('id');
    Route::get('/sertifikat-tanah/export', [LandCertificateController::class, 'export'])->name('sertifikat-tanah.export');
    Route::get('/sertifikat-tanah/pdf', [LandCertificateController::class, 'exportPdf'])->name('sertifikat-tanah.pdf');

    // Master Data UPT
    Route::get('/upt', [UptManagementController::class, 'index'])->name('upt.index');
    Route::get('/upt/pdf', [UptManagementController::class, 'exportPdf'])->name('upt.pdf');
    Route::get('/upt/create', [UptManagementController::class, 'create'])->name('upt.create');
    Route::post('/upt', [UptManagementController::class, 'store'])->name('upt.store');
    Route::get('/upt/{id}', [UptManagementController::class, 'show'])->name('upt.show')->whereNumber('id');
    Route::get('/upt/{id}/edit', [UptManagementController::class, 'edit'])->name('upt.edit')->whereNumber('id');
    Route::put('/upt/{id}', [UptManagementController::class, 'update'])->name('upt.update')->whereNumber('id');
    Route::get('/upt/{id}/registri', [FamilyCardManagementController::class, 'page'])->name('upt.registri')->whereNumber('id');

    // Menu Kelola Data: Penempatan Awal
    Route::get('/placements', [PlacementManagementController::class, 'index'])->name('placements.index');
    Route::put('/placements/{id}', [PlacementManagementController::class, 'update'])->name('placements.update')->whereNumber('id');
    Route::get('/placements/export', [PlacementManagementController::class, 'export'])->name('placements.export');
    Route::get('/placements/pdf', [PlacementManagementController::class, 'exportPdf'])->name('placements.pdf');

    // Menu Kelola Data: Serah Terima Pemda
    Route::get('/handovers', [HandoverManagementController::class, 'index'])->name('handovers.index');
    Route::put('/handovers/{id}', [HandoverManagementController::class, 'update'])->name('handovers.update')->whereNumber('id');
    Route::get('/handovers/export', [HandoverManagementController::class, 'export'])->name('handovers.export');
    Route::get('/handovers/pdf', [HandoverManagementController::class, 'exportPdf'])->name('handovers.pdf');

    // Antrean Verifikasi Draf Usulan & Diff Checker
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{id}', [VerificationController::class, 'show'])->name('verification.show')->whereNumber('id');
    Route::post('/verification/{id}/approve', [VerificationController::class, 'approve'])->name('verification.approve')->whereNumber('id');
    Route::post('/verification/{id}/reject', [VerificationController::class, 'reject'])->name('verification.reject')->whereNumber('id');

    // Repositori E-Arsip BAST
    Route::get('/documents', [DocumentRepositoryController::class, 'index'])->name('documents.index');
    Route::get('/documents/pdf', [DocumentRepositoryController::class, 'exportPdf'])->name('documents.pdf');
    Route::post('/documents/upload', [DocumentRepositoryController::class, 'upload'])->name('documents.upload');
    Route::get('/documents/{id}/download', [DocumentRepositoryController::class, 'download'])->name('documents.download')->whereNumber('id');
    Route::delete('/documents/{id}', [DocumentRepositoryController::class, 'destroy'])->name('documents.destroy')->whereNumber('id');

    // Menu Kelola Data: Pengaturan Wilayah Peta WebGIS
    Route::get('/regencies', [RegencyController::class, 'index'])->name('regencies.index');
    Route::get('/regencies/pdf', [RegencyController::class, 'exportPdf'])->name('regencies.pdf');
    Route::patch('/regencies/{id}/toggle', [RegencyController::class, 'toggle'])->name('regencies.toggle')->whereNumber('id');
    Route::post('/regencies/bulk-visibility', [RegencyController::class, 'bulkVisibility'])->name('regencies.bulk-visibility');
    Route::put('/regencies/{id}', [RegencyController::class, 'update'])->name('regencies.update')->whereNumber('id');

    // Laporan & Ekspor Resmi
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

    // Kontrol & Keamanan: Manajemen Pengguna (RBAC)
    Route::resource('users', UserController::class);
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Kontrol & Keamanan: Log Audit (Audit Trail)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export', [AuditLogController::class, 'exportCsv'])->name('audit-logs.export');
    Route::get('/audit-logs/pdf', [AuditLogController::class, 'exportPdf'])->name('audit-logs.pdf');

    // Kontrol & Keamanan: Cadangan Database (Backup)
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backup/create', [BackupController::class, 'createBackup'])->name('backup.create');
    Route::get('/backup/{filename}/download', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');
    Route::post('/backup/test-integrity', [BackupController::class, 'testIntegrity'])->name('backup.test-integrity');
});

// Modul Registri Warga (Opsi C Hibrida) - Akses Bersama Super Admin & Operator Wilayah
Route::middleware(['auth', 'role:super_admin,eksekutif,mitra_bpn,operator_kabupaten'])->prefix('admin/family-cards')->name('admin.family-cards.')->group(function () {
    Route::get('/upt/{uptId}', [FamilyCardManagementController::class, 'list'])->name('list')->whereNumber('uptId');
    Route::post('/upt/{uptId}', [FamilyCardManagementController::class, 'store'])->name('store')->whereNumber('uptId');
    Route::match(['put', 'post'], '/{id}', [FamilyCardManagementController::class, 'update'])->name('update')->whereNumber('id');
    Route::get('/{id}/document', [FamilyCardManagementController::class, 'downloadDocument'])->name('document')->whereNumber('id');
    Route::delete('/{id}', [FamilyCardManagementController::class, 'destroy'])->name('destroy')->whereNumber('id');
    Route::get('/template/download', [FamilyCardManagementController::class, 'downloadTemplate'])->name('template.download');
    Route::post('/upt/{uptId}/import', [FamilyCardManagementController::class, 'import'])->name('import')->whereNumber('uptId');
    Route::get('/upt/{uptId}/export', [FamilyCardManagementController::class, 'export'])->name('export')->whereNumber('uptId');
    Route::post('/upt/{uptId}/sync-aggregate', [FamilyCardManagementController::class, 'syncAggregate'])->name('sync-aggregate')->whereNumber('uptId');
    Route::post('/upt/{uptId}/submit-validation', [FamilyCardManagementController::class, 'submitValidation'])->name('submit-validation')->whereNumber('uptId');
});

// 2. MODUL ANTARMUKA OPERATOR WILAYAH (LINTAS 9 KABUPATEN)
Route::middleware(['auth', 'role:operator_kabupaten'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/dashboard', [OperatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/upt', [OperatorUptController::class, 'index'])->name('upt.index');
    Route::get('/upt/{id}/registri', [OperatorUptController::class, 'registri'])->name('upt.registri')->whereNumber('id');
    Route::get('/requests', [OperatorChangeRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [OperatorChangeRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [OperatorChangeRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{id}', [OperatorChangeRequestController::class, 'show'])->name('requests.show')->whereNumber('id');
    Route::get('/documents', [DocumentRepositoryController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}/download', [DocumentRepositoryController::class, 'download'])->name('documents.download')->whereNumber('id');
});


// Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
