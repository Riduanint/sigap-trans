@extends('layouts.admin')

@section('title', 'Log Audit Sistem (Audit Trail Forensik)')
@section('header_title', 'Rekam Jejak Aktivitas Sistem (Audit Trail Log)')
@section('header_subtitle', 'Pencatatan forensik untuk seluruh aksi perubahan data, persetujuan verifikasi, unggah arsip, dan ekspor data kedinasan')

@section('content')
<div class="space-y-6">

    <!-- 1. FILTER RENTANG TANGGAL, AKSI, DAN EKSPOR CSV -->
    <div class="bg-white rounded-2xl p-5 border border-[#C9C1B1]/70 shadow-ambient-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            
            <!-- Tanggal Mulai -->
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-[#2C3B4D]/70">Dari:</span>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
            </div>

            <!-- Tanggal Selesai -->
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-[#2C3B4D]/70">s.d:</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#1B2632]">
            </div>

            <!-- Filter Aksi -->
            <select name="action" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                <option value="">-- Semua Aksi Sistem --</option>
                @foreach($availableActions as $act)
                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                        {{ $act }}
                    </option>
                @endforeach
            </select>

            <!-- Filter Pengguna -->
            <select name="user_id" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-[#C9C1B1] focus:border-[#FFB162] focus:ring focus:ring-[#FFB162]/20 shadow-xs text-[#2C3B4D]">
                <option value="">-- Semua Pengguna --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#1B2632] hover:bg-[#2C3B4D] text-[#EEE9DF] text-xs font-bold shadow-ambient-xs transition">
                Terapkan
            </button>

            @if(request()->anyFilled(['start_date', 'end_date', 'action', 'user_id', 'search']))
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-bold text-[#A35139] hover:underline px-1">
                    Reset
                </a>
            @endif
        </form>

        <!-- Tombol Ekspor CSV -->
        <a href="{{ route('admin.audit-logs.export', request()->query()) }}" 
           class="bg-[#A35139] hover:bg-[#8A4430] text-[#EEE9DF] font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-ambient-xs flex items-center gap-2 self-start lg:self-auto">
            <svg class="w-4 h-4 text-[#FFB162]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Unduh Log (CSV)</span>
        </a>
    </div>

    <!-- 2. TABEL REKAM JEJAK LOG AUDIT -->
    <div class="bg-white rounded-2xl border border-[#C9C1B1]/70 shadow-ambient-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#2C3B4D]">
                <thead class="bg-[#1B2632] text-[#EEE9DF] uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Waktu Kejadian (WITA)</th>
                        <th class="px-4 py-3.5">Pengguna & Peran</th>
                        <th class="px-4 py-3.5">Aksi Sistem</th>
                        <th class="px-4 py-3.5">Sasaran & Target ID</th>
                        <th class="px-4 py-3.5">Alamat IP & User Agent</th>
                        <th class="px-4 py-3.5 text-center">Rincian Forensik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C9C1B1]/30">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-[#EEE9DF]/40 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-[#2C3B4D]/50 tabular-nums">
                                {{ $logs->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-[#2C3B4D]/70 whitespace-nowrap">
                                {{ $log->created_at?->format('d/m/Y H:i:s') ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-extrabold text-[#1B2632] block">{{ $log->user->name ?? 'System Process' }}</span>
                                <span class="text-[10px] text-[#2C3B4D]/60 font-semibold">{{ $log->user->role ?? 'system' }}</span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $actionColor = match(true) {
                                        str_contains($log->action, 'APPROVE') => 'bg-[#2C3B4D]/15 text-[#2C3B4D] border-[#2C3B4D]/30',
                                        str_contains($log->action, 'REJECT') => 'bg-[#A35139]/15 text-[#A35139] border-[#A35139]/30',
                                        str_contains($log->action, 'CREATE') || str_contains($log->action, 'SUBMIT') => 'bg-[#1B2632]/10 text-[#1B2632] border-[#1B2632]/20',
                                        str_contains($log->action, 'UPDATE') => 'bg-[#FFB162]/20 text-[#8F4E0A] border-[#FFB162]/40',
                                        str_contains($log->action, 'DELETE') => 'bg-[#A35139]/20 text-[#A35139] border-[#A35139]/40',
                                        default => 'bg-[#EEE9DF] text-[#2C3B4D] border-[#C9C1B1]/60',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $actionColor }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono text-[#1B2632] font-bold block">{{ $log->target_table }}</span>
                                <span class="text-[10px] text-[#2C3B4D]/60">ID: {{ $log->target_id }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-[#2C3B4D]/70 max-w-xs truncate">
                                <div class="font-mono text-[10px] text-[#1B2632] font-semibold">{{ $log->ip_address }}</div>
                                <div class="text-[9px] text-[#2C3B4D]/50 truncate">{{ $log->user_agent }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if(!empty($log->details))
                                    <button type="button" onclick="showAuditModal({{ json_encode($log) }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#EEE9DF] hover:bg-[#C9C1B1]/50 text-[#1B2632] border border-[#C9C1B1]/60 font-bold text-[11px] transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>Rincian</span>
                                    </button>
                                @else
                                    <span class="text-[10px] text-[#2C3B4D]/40 italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-[#2C3B4D]/60 font-medium">
                                Belum ada rekam jejak aktivitas yang sesuai filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-[#C9C1B1]/40 bg-[#EEE9DF]/20">
                {{ $logs->links('admin.partials.pagination') }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL RINCIAN FORENSIK JSON -->
<div id="audit-modal" class="fixed inset-0 z-50 hidden bg-[#1B2632]/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-[#C9C1B1] overflow-hidden">
        <div class="p-5 bg-[#1B2632] text-[#EEE9DF] flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[#EEE9DF]" id="modal-title">Rincian Log Audit</h3>
                <span class="text-[11px] text-[#FFB162] font-mono" id="modal-subtitle"></span>
            </div>
            <button type="button" onclick="closeAuditModal()" class="text-[#EEE9DF]/70 hover:text-white p-1 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4">
            <div>
                <span class="text-xs font-bold text-[#2C3B4D] uppercase tracking-wider block mb-1">Payload JSON Terenkripsi:</span>
                <pre id="modal-json" class="bg-[#1B2632] text-[#FFB162] p-4 rounded-xl text-xs font-mono overflow-x-auto max-h-96"></pre>
            </div>
        </div>

        <div class="p-4 bg-[#EEE9DF]/40 border-t border-[#C9C1B1]/40 flex justify-end shrink-0">
            <button type="button" onclick="closeAuditModal()" class="px-4 py-2 rounded-xl bg-white hover:bg-[#EEE9DF] text-xs font-bold text-[#1B2632] border border-[#C9C1B1]/70 transition">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<script>
function showAuditModal(log) {
    document.getElementById('modal-title').innerText = 'Log Forensik: ' + log.action;
    document.getElementById('modal-subtitle').innerText = 'Tabel ' + log.target_table + ' #' + log.target_id + ' • IP: ' + log.ip_address;
    document.getElementById('modal-json').innerText = JSON.stringify(log.details, null, 2);
    document.getElementById('audit-modal').classList.remove('hidden');
}

function closeAuditModal() {
    document.getElementById('audit-modal').classList.add('hidden');
}
</script>
@endsection
