<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upt_change_requests', function (Blueprint $table) {
            $table->id(); // BIGSERIAL PRIMARY KEY
            $table->foreignId('upt_location_id')->constrained('upt_locations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()->comment('Operator pengaju');
            $table->string('request_type', 50)->comment("DATA_UPDATE, LEGAL_ISSUE, NEW_BAST");
            $table->jsonb('proposed_payload')->comment('Snapshot JSON data baru yang diusulkan');
            $table->string('status', 20)->default('pending');
            $table->text('reviewer_note')->nullable()->comment('Catatan revisi atau alasan verifikasi');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Super Admin verifikator');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indeks alur kerja verifikasi
            $table->index('status');
            $table->index('user_id');
            $table->index('upt_location_id');
        });

        try {
            DB::statement("ALTER TABLE upt_change_requests ADD CONSTRAINT check_change_request_status CHECK (status IN ('pending', 'approved', 'rejected'))");
        } catch (\Throwable $e) {
            // Abaikan jika tidak didukung
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upt_change_requests');
    }
};
