<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ruang Kendali') - SIGAP-TRANS KALSEL</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom subtle scrollbar for sidebar */
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: #0B1849;
        }

        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #E4B028;
        }
    </style>
</head>

<body
    class="bg-[#EBEDE3] text-slate-800 font-sans antialiased min-h-screen lg:h-screen lg:overflow-hidden flex flex-col">

    <div class="flex-1 flex flex-col lg:flex-row min-h-screen lg:min-h-0 lg:h-full lg:overflow-hidden">

        <!-- 1. SIDEBAR NAVIGASI (MIDNIGHT NAVY: #0B1849) -->
        <aside
            class="w-full lg:w-72 bg-[#0B1849] text-white flex flex-col shrink-0 shadow-2xl z-30 lg:h-full lg:overflow-y-auto custom-sidebar-scroll">

            <!-- Brand & Identitas Kedinasan -->
            <div class="p-5 border-b border-slate-700/60 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#124D1C] to-emerald-700 border border-[#E4B028]/40 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base tracking-tight text-white leading-none">
                            SIGAP-TRANS <span class="text-[#E4B028]">KALSEL</span>
                        </h1>
                        <span class="text-[10px] text-slate-400 block mt-0.5 tracking-wider uppercase font-semibold">
                            Disnakertrans Prov. Kalsel
                        </span>
                    </div>
                </div>
            </div>

            <!-- Profil Aparatur Berdasarkan Role -->
            @php
                $currentUser = Auth::user();
                $roleLabel = match ($currentUser?->role) {
                    'super_admin' => 'Super Admin (Provinsi)',
                    'operator_kabupaten' => 'Operator ' . ($currentUser?->regency?->name ?? 'Kabupaten'),
                    'eksekutif' => 'Eksekutif Pimpinan',
                    'mitra_bpn' => 'Mitra Kanwil ATR/BPN',
                    default => 'Aparatur Sipil Negara'
                };
                $initials = match ($currentUser?->role) {
                    'super_admin' => 'SA',
                    'operator_kabupaten' => 'OP',
                    'eksekutif' => 'EK',
                    'mitra_bpn' => 'BP',
                    default => 'ST'
                };
                $pendingApprovals = \App\Models\UptChangeRequest::where('status', 'pending')->count();
            @endphp

            <div class="p-4 mx-3 my-3 rounded-xl bg-white/5 border border-white/10 flex items-center gap-3 shrink-0">
                <div
                    class="w-10 h-10 rounded-full bg-[#124D1C] border-2 border-[#E4B028] flex items-center justify-center font-bold text-sm text-white shrink-0 shadow">
                    {{ $initials }}
                </div>
                <div class="overflow-hidden">
                    <div class="font-bold text-xs text-white truncate">{{ $currentUser->name ?? 'Aparatur Pemprov' }}
                    </div>
                    <div class="text-[10px] text-[#E4B028] font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#E4B028] animate-ping"></span>
                        <span>{{ $roleLabel }}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">NIP: {{ $currentUser->nip ?? '-' }}</div>
                </div>
            </div>

            <!-- Menu Navigasi Utama -->
            <nav class="flex-1 px-3 py-2 space-y-1.5 text-xs font-medium">
                <div class="px-3 pt-2 pb-1 text-[10px] uppercase font-extrabold tracking-wider text-slate-400">
                    Menu Kelola Data
                </div>

                <!-- ==================== MENU SUPER ADMIN ==================== -->
                @if($currentUser?->role === 'super_admin')
                    <!-- Dashboard Utama -->
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        <span>Dashboard Utama</span>
                    </a>

                    <!-- Master Data 124 UPT -->
                    <a href="{{ route('admin.upt.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.upt.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.upt.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                </path>
                            </svg>
                            <span>Master Data 124 UPT</span>
                        </div>
                        <span class="bg-[#E4B028] text-[#0B1849] text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                            124
                        </span>
                    </a>

                    <!-- Penempatan Awal (Kelola KK) -->
                    <a href="{{ route('admin.placements.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.placements.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.placements.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span>Penempatan Awal</span>
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-emerald-500/30">
                            63.716 KK
                        </span>
                    </a>

                    <!-- Serah Terima Pemda (Kelola KK) -->
                    <a href="{{ route('admin.handovers.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.handovers.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.handovers.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>Serah Terima Pemda</span>
                        </div>
                        <span class="bg-amber-500/20 text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-500/30">
                            64.957 KK
                        </span>
                    </a>

                    <!-- Antrean Verifikasi Draf (Diff Checker) -->
                    <a href="{{ route('admin.verification.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.verification.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.verification.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                </path>
                            </svg>
                            <span>Verifikasi Draf Usulan</span>
                        </div>
                        @if($pendingApprovals > 0)
                            <span class="bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full animate-pulse">
                                {{ $pendingApprovals }}
                            </span>
                        @endif
                    </a>

                    <!-- Repositori E-Arsip BAST -->
                    <a href="{{ route('admin.documents.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.documents.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.documents.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>Repositori E-Arsip BAST</span>
                    </a>

                    <!-- Pengaturan Wilayah Peta WebGIS -->
                    <a href="{{ route('admin.regencies.index') }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.regencies.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.regencies.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                                </path>
                            </svg>
                            <span>Wilayah Peta WebGIS</span>
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-1.5 py-0.5 rounded border border-emerald-500/30">
                            9 Kab
                        </span>
                    </a>

                    <div class="px-3 pt-4 pb-1 text-[10px] uppercase font-extrabold tracking-wider text-slate-400">
                        Kontrol & Keamanan
                    </div>

                    <!-- Manajemen Pengguna (RBAC) -->
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span>Manajemen Pengguna (RBAC)</span>
                    </a>

                    <!-- Log Audit (Audit Trail) -->
                    <a href="{{ route('admin.audit-logs.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.audit-logs.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span>Log Audit (Audit Trail)</span>
                    </a>

                    <!-- Cadangan Database (Backup) -->
                    <a href="{{ route('admin.backup.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.backup.*') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.backup.*') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                        </svg>
                        <span>Cadangan Database (Backup)</span>
                    </a>

                    <!-- ==================== MENU OPERATOR KABUPATEN ==================== -->
                @elseif($currentUser?->role === 'operator_kabupaten')
                    <!-- Dashboard Kabupaten -->
                    <a href="{{ route('operator.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('operator.dashboard') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('operator.dashboard') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <span>Dasbor Kabupaten</span>
                    </a>

                    <!-- Riwayat Pengajuan Draf -->
                    <a href="{{ route('operator.requests.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('operator.requests.index') || request()->routeIs('operator.requests.show') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('operator.requests.index') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        <span>Riwayat Usulan Draf</span>
                    </a>

                    <!-- Ajukan Draf Baru -->
                    <a href="{{ route('operator.requests.create') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('operator.requests.create') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('operator.requests.create') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Ajukan Draf Baru</span>
                    </a>

                    <!-- ==================== MENU EKSEKUTIF ==================== -->
                @elseif($currentUser?->role === 'eksekutif')
                    <a href="{{ route('executive.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('executive.dashboard') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('executive.dashboard') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        <span>Ruang Kendali Eksekutif</span>
                    </a>

                    <!-- ==================== MENU MITRA BPN ==================== -->
                @elseif($currentUser?->role === 'mitra_bpn')
                    <a href="{{ route('bpn.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('bpn.dashboard') ? 'bg-[#124D1C] text-white font-bold border-l-4 border-[#E4B028] shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('bpn.dashboard') ? 'text-[#E4B028]' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span>Portal Pertanahan SHM</span>
                    </a>
                @endif

                <div class="px-3 pt-4 pb-1 text-[10px] uppercase font-extrabold tracking-wider text-slate-400">
                    Akses Eksternal
                </div>

                <!-- Peta WebGIS Publik -->
                <a href="{{ url('/') }}" target="_blank"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                            </path>
                        </svg>
                        <span>Portal Peta WebGIS</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </nav>

            <!-- Tombol Pengaturan & Keluar (Sesuai Wireframe) -->
            <div class="p-3 border-t border-slate-700/60 mt-auto shrink-0 flex items-center gap-2">
                <a href="{{ route('profile.edit') }}"
                    class="flex-1 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 text-xs font-bold py-2 px-2 rounded-xl transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Pengaturan</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full bg-white/5 hover:bg-rose-900/60 text-slate-300 hover:text-white border border-white/10 hover:border-rose-500/40 text-xs font-bold py-2 px-2 rounded-xl transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- 2. KONTEN LEMBAR KERJA UTAMA (WARM ALABASTER: #EBEDE3) -->
        <main class="flex-1 flex flex-col min-w-0 lg:h-full lg:overflow-y-auto">

            <!-- Topbar Header Konten -->
            <header
                class="bg-white border-b border-slate-200/80 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20 shadow-xs">
                <div>
                    <h2 class="text-xl font-black text-[#0B1849] tracking-tight">
                        @yield('header_title', 'Ruang Kendali SIGAP-TRANS')
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @yield('header_subtitle', 'Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi')
                    </p>
                </div>

                <!-- Aksi Cepat Cetak & Ekspor Resmi Sesuai Role -->
                <div class="flex items-center gap-2.5">
                    @if($currentUser?->role === 'operator_kabupaten')
                        <a href="{{ route('operator.requests.create') }}"
                            class="bg-[#124D1C] hover:bg-emerald-800 text-white font-black text-xs px-4 py-2 rounded-xl transition shadow-xs flex items-center gap-1.5 border border-[#E4B028]">
                            <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            <span>Ajukan Draf Usulan</span>
                        </a>
                    @else
                        <a href="{{ route('admin.reports.excel') }}"
                            class="bg-[#E4B028] hover:bg-[#d4a020] text-[#0B1849] font-extrabold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5 border border-[#E4B028]/60">
                            <svg class="w-4 h-4 text-[#0B1849]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span>Ekspor Excel</span>
                        </a>
                        <a href="{{ route('admin.reports.pdf') }}" target="_blank"
                            class="bg-[#0B1849] hover:bg-slate-800 text-white font-extrabold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            <span>Cetak PDF Resmi</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Notifikasi Alert Flash Message -->
            @if(session('success'))
                <div
                    class="mx-6 mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-bold flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div
                    class="mx-6 mt-4 p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-bold flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Body Isi Halaman -->
            <div class="p-6">
                @yield('content')
            </div>

            <!-- Footer Kedinasan -->
            <footer
                class="mt-auto px-6 py-4 border-t border-slate-200/80 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    <strong>SIGAP-TRANS KALSEL</strong> &copy; {{ date('Y') }} Dinas Tenaga Kerja dan Transmigrasi
                    Provinsi Kalimantan Selatan.
                </div>
                <div class="text-[11px] text-slate-400">
                    Engine: PostgreSQL 18 + PostGIS • Framework: Laravel 11 • Font: Inter
                </div>
            </footer>
        </main>
    </div>

</body>

</html>