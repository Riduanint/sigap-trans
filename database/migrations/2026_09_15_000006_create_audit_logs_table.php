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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); // BIGSERIAL PRIMARY KEY
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->comment('LOGIN, APPROVE_DRAFT, REJECT_DRAFT, EXPORT_XLS, dll');
            $table->string('target_table', 50)->comment('Tabel sasaran tindakan, contoh: upt_locations');
            $table->string('target_id', 100)->comment('ID entitas yang diubah/dihapus');
            $table->jsonb('details')->nullable()->comment('Payload data sebelum dan sesudah perubahan (diff)');
            $table->string('ip_address', 45)->comment('Alamat IP pengguna');
            $table->text('user_agent')->nullable()->comment('Informasi peramban');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Indeks audit trail forensik
            $table->index('user_id');
            $table->index('action');
            $table->index(['target_table', 'target_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
