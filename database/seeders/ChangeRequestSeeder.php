<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\UptChangeRequest;
use App\Models\UptLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChangeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operatorTapin = User::where('email', 'operator.tapin@kalselprov.go.id')->first();
        $superAdmin = User::where('email', 'superadmin@kalselprov.go.id')->first();

        if (! $operatorTapin || ! $superAdmin) {
            return;
        }

        // Cari UPT di Tapin (regency_id = 1)
        $tapinUpts = UptLocation::where('regency_id', 1)->orderBy('upt_number')->get();
        if ($tapinUpts->isEmpty()) {
            return;
        }

        // 1. Draf Pending (Menunggu Verifikasi Diff Checker)
        $targetPending = $tapinUpts->first();
        $pendingRequest = UptChangeRequest::updateOrCreate(
            [
                'upt_location_id' => $targetPending->id,
                'request_type' => 'DATA_UPDATE',
                'status' => 'pending',
            ],
            [
                'user_id' => $operatorTapin->id,
                'proposed_payload' => [
                    'submission_note' => 'Hasil verifikasi lapangan Triwulan III 2026 bersama Tim GTRA Kabupaten Tapin: status kepemilikan sertifikat SHM warga telah tuntas 100% dan nama desa definitif telah disahkan perda.',
                    'current_village_name' => $targetPending->current_village_name . ' Definitif',
                    'business_pattern' => 'Pola TPLB (Perkebunan Terpadu)',
                    'placement_year' => $targetPending->placement_year,
                    'placement_kk' => $targetPending->placement_kk + 15,
                    'placement_population' => $targetPending->placement_population + 45,
                    'handover_year' => '2025',
                    'handover_kk' => $targetPending->handover_kk + 15,
                    'handover_population' => $targetPending->handover_population + 45,
                    'issue_status' => 'clean',
                    'issue_note' => 'Sertifikasi SHM tuntas 100%, fasos/fasum diserahkan penuh ke Pemda Tapin.',
                    'shm_status' => '100% SHM',
                ],
                'reviewer_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'created_at' => now()->subHours(4),
            ]
        );

        // 2. Draf Approved (Sudah Disetujui)
        if ($tapinUpts->count() > 1) {
            $targetApproved = $tapinUpts->get(1);
            UptChangeRequest::updateOrCreate(
                [
                    'upt_location_id' => $targetApproved->id,
                    'request_type' => 'LEGAL_ISSUE',
                    'status' => 'approved',
                ],
                [
                    'user_id' => $operatorTapin->id,
                    'proposed_payload' => [
                        'submission_note' => 'Penyesuaian status tata ruang pasca penyelesaian mediasi tapal batas desa.',
                        'current_village_name' => $targetApproved->current_village_name,
                        'business_pattern' => $targetApproved->business_pattern,
                        'placement_year' => $targetApproved->placement_year,
                        'placement_kk' => $targetApproved->placement_kk,
                        'handover_year' => $targetApproved->handover_year,
                        'handover_kk' => $targetApproved->handover_kk,
                        'issue_status' => 'clean',
                        'issue_note' => 'Mediasi tapal batas selesai. Status clean & clear.',
                        'shm_status' => '100% SHM',
                    ],
                    'reviewer_note' => 'Disetujui. Berita acara kesepakatan tapal batas telah diverifikasi.',
                    'reviewed_by' => $superAdmin->id,
                    'reviewed_at' => now()->subDays(2),
                    'created_at' => now()->subDays(3),
                ]
            );
        }

        // Catat jejak audit
        AuditLog::log('SEED_CHANGE_REQUESTS', 'upt_change_requests', $pendingRequest->id, [
            'description' => 'Initial sample change requests for testing diff checker workflow',
        ]);
    }
}
