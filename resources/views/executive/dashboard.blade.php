@extends('layouts.admin')

@section('title', 'Ruang Kendali Eksekutif - Kepala Dinas / Kabid')
@section('header_title', 'Ruang Kendali Eksekutif Pimpinan')
@section('header_subtitle', 'Sistem Monitoring Geospasial, Kebijakan Mediasi Sengketa Lahan, & Rekapitulasi Strategis Transmigrasi Kalsel')

@section('content')
<div class="space-y-6">

    <!-- 1. BANNER EKSEKUTIF PIMPINAN -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0B1849] via-slate-900 to-[#124D1C] p-6 text-white shadow-xl border border-white/10 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-[#E4B028]/10 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#124D1C] border-2 border-[#E4B028] flex items-center justify-center text-[#E4B028] font-black text-xl shrink-0 shadow-lg">
                    EK
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#E4B028] bg-[#E4B028]/20 px-2.5 py-0.5 rounded-full border border-[#E4B028]/30">
                            Akses Eksekutif Pimpinan
                        </span>
                        <span class="text-xs text-slate-300 font-mono">Disnakertrans Prov. Kalsel</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-white mt-1">
                        {{ $user->name ?? 'Hj. Ina Yuliani, S.Sos, M.Si, M.IP' }}
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        {{ $user->position ?? 'Kepala Bidang Ketransmigrasian' }} • NIP: {{ $user->nip ?? '196907291990102001' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.pdf') }}" target="_blank"
                   class="bg-[#E4B028] hover:bg-[#d4a020] text-[#0B1849] font-black text-xs px-4 py-2.5 rounded-xl transition shadow-lg flex items-center gap-2 border border-[#E4B028]">
                    <svg class="w-4 h-4 text-[#0B1849]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak Laporan Eksekutif (PDF)</span>
                </a>
                <a href="{{ url('/') }}" target="_blank"
                   class="bg-white/10 hover:bg-white/20 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition border border-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    <span>Peta Lengkap Se-Kalsel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. 4 KARTU KPI UTAMA PROVINSI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Unit Permukiman</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#0B1849] tabular-nums">{{ $totalUpt }}</span>
                <span class="text-xs text-slate-500 font-semibold">UPT Tersebar</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Mencakup 9 Kabupaten Se-Kalimantan Selatan
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Realisasi Penempatan</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#124D1C] tabular-nums">{{ number_format($totalPlacementKk, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 font-semibold">KK</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Akumulasi warga: <span class="font-bold text-slate-700">{{ number_format($totalPlacementPop, 0, ',', '.') }} Jiwa</span>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Serah Terima ke Pemda</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-blue-800 tabular-nums">{{ number_format($totalHandoverKk, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 font-semibold">KK</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500">
                Akumulasi warga: <span class="font-bold text-slate-700">{{ number_format($totalHandoverPop, 0, ',', '.') }} Jiwa</span>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status Hukum Agraria</span>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-rose-600 tabular-nums">{{ $criticalCount }}</span>
                <span class="text-xs text-slate-500 font-semibold">Kasus Kritis Sengketa</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-2">
                <span class="text-emerald-700 font-bold">🟢 {{ $cleanCount }} Clean</span>
                <span>•</span>
                <span class="text-amber-700 font-bold">🟡 {{ $warningCount }} Warning</span>
            </div>
        </div>
    </div>

    <!-- 3. PETA SPASIAL KHUSUS 6 KASUS PRIORITAS MEDIASI (PIN MERAH BERDENYUT) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 bg-[#0B1849] text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-600 flex items-center justify-center text-white shadow-md animate-pulse">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-black tracking-tight text-white uppercase flex items-center gap-2">
                        <span>Peta Tematik Spasial: 6 Kasus Prioritas Mediasi Sengketa</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white">
                            Action Plan Pimpinan
                        </span>
                    </h3>
                    <p class="text-xs text-slate-300">
                        Lokasi tumpang tindih kawasan hutan (KPHP/HPT) dan perizinan tambang batubara yang membutuhkan koordinasi lintas instansi
                    </p>
                </div>
            </div>
            <div class="text-xs text-slate-300">
                Koordinasi: Ditjen Bina Trans • KLHK • Kanwil BPN
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div class="relative">
            <div id="executive-map" class="w-full h-[400px] z-10"></div>
        </div>

        <!-- Rincian 6 Kasus Kritis Grid -->
        <div class="p-6 bg-slate-50 border-t border-slate-200">
            <h4 class="text-xs font-black text-[#0B1849] uppercase tracking-wider mb-4 flex items-center gap-2">
                <span>Daftar Evaluasi Masalah Lapangan & Rekomendasi Mediasi:</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($criticalCases as $case)
                    <div class="p-4 rounded-xl bg-white border border-rose-200 shadow-xs hover:border-rose-400 transition space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                    UPT #{{ $case->upt_number }}
                                </span>
                                <h5 class="text-xs font-extrabold text-[#0B1849] mt-1">{{ $case->upt_name }}</h5>
                                <div class="text-[11px] text-slate-500 font-medium">Desa: {{ $case->current_village_name }} ({{ $case->regency->name }})</div>
                            </div>
                            <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                        </div>

                        <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <strong>Uraian Masalah:</strong><br>
                            {{ $case->issue_note ?? 'Tumpang tindih kawasan hutan atau batas konsesi tambang.' }}
                        </div>

                        <div class="text-[11px] text-emerald-800 bg-emerald-50 p-2 rounded-lg border border-emerald-200">
                            <strong>Rekomendasi Pimpinan:</strong><br>
                            Gelar Rapat Koordinasi Bersama BPN & Balai Pemantapan Kawasan Hutan (BPKH).
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4. TABEL AGREGASI SEBARAN 9 KABUPATEN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-[#0B1849] tracking-tight">
                    Rekapitulasi Sebaran Transmigrasi 9 Kabupaten
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Data agregat kependudukan wilayah I sampai dengan IX se-Kalimantan Selatan
                </p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                9 Satuan Wilayah
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#0B1849] text-white uppercase font-extrabold text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-16">Wilayah</th>
                        <th class="px-5 py-3.5">Nama Kabupaten</th>
                        <th class="px-5 py-3.5 text-center">Jumlah UPT</th>
                        <th class="px-5 py-3.5 text-right">KK Penempatan</th>
                        <th class="px-5 py-3.5 text-right">KK Serah Terima</th>
                        <th class="px-5 py-3.5 text-center">Persentase Otonomi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($regenciesSummary as $reg)
                        @php
                            $perc = ($reg->total_placement_kk > 0) ? round(($reg->total_handover_kk / $reg->total_placement_kk) * 100, 1) : 100;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3 text-center font-bold text-slate-400 font-mono">
                                {{ $reg->roman_code }}
                            </td>
                            <td class="px-5 py-3 font-bold text-[#0B1849]">
                                {{ $reg->name }}
                            </td>
                            <td class="px-5 py-3 text-center font-extrabold tabular-nums">
                                {{ $reg->upt_locations_count }} UPT
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-[#124D1C] tabular-nums">
                                {{ number_format($reg->total_placement_kk, 0, ',', '.') }} KK
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-blue-900 tabular-nums">
                                {{ number_format($reg->total_handover_kk, 0, ',', '.') }} KK
                            </td>
                            <td class="px-5 py-3 text-center tabular-nums">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $perc >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $perc }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Leaflet CSS & JS for Executive Thematic Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('executive-map', {
        center: [-3.0926, 115.2838],
        zoom: 8,
        zoomControl: true,
        scrollWheelZoom: false
    });

    // Basemap Carto Voyager
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '&copy; CartoDB & OpenStreetMap'
    }).addTo(map);

    // Data 6 kasus kritis dari server
    const criticalCases = @json($criticalCases);

    const redIcon = L.divIcon({
        className: 'custom-pin-critical',
        html: `
            <div style="position: relative; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; width: 28px; height: 28px; border-radius: 50%; background: rgba(225, 29, 72, 0.4); animation: pulse-ring 1.5s infinite;"></div>
                <div style="width: 18px; height: 18px; border-radius: 50%; background: #e11d48; border: 2.5px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"></div>
            </div>
        `,
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    const bounds = L.latLngBounds();

    criticalCases.forEach(item => {
        if (item.latitude && item.longitude) {
            const latLng = [parseFloat(item.latitude), parseFloat(item.longitude)];
            bounds.extend(latLng);

            const marker = L.marker(latLng, { icon: redIcon }).addTo(map);
            marker.bindPopup(`
                <div style="font-family: 'Inter', sans-serif; font-size: 11px; max-width: 240px; padding: 4px;">
                    <div style="font-weight: 800; color: #0B1849; margin-bottom: 2px;">
                        [No. ${item.upt_number}] ${item.upt_name}
                    </div>
                    <div style="color: #64748b; font-size: 10px; margin-bottom: 6px;">
                        Desa: ${item.current_village_name} (${item.regency ? item.regency.name : ''})
                    </div>
                    <div style="background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; padding: 6px; border-radius: 6px; font-size: 10px; margin-bottom: 6px;">
                        <strong>Sengketa:</strong> ${item.issue_note || 'Overlap kawasan hutan / tambang'}
                    </div>
                    <div style="font-size: 9px; color: #047857; font-weight: 700;">
                        Target Mediasi: Ditjen Bina Trans & KLHK
                    </div>
                </div>
            `);
        }
    });

    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
});
</script>

<style>
@keyframes pulse-ring {
    0% { transform: scale(0.6); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}
</style>
@endsection
