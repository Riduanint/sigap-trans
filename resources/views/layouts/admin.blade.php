@php
    $user = auth()->user();
    $isOperator = $user && $user->role === 'operator_kabupaten';
    $pendingCount = $isOperator ? 0 : \App\Models\UptChangeRequest::where('status', 'pending')->count();
    $populationPage = request()->routeIs('admin.placements.*', 'admin.handovers.*', 'admin.upt.registri');
    $administrationPage = request()->routeIs('admin.users.*', 'admin.regencies.*', 'admin.audit-logs.*', 'admin.backup.*');
    $homeRoute = $isOperator ? 'operator.dashboard' : 'admin.dashboard';
    $pageName = match (true) {
        $isOperator && request()->routeIs('operator.dashboard') => 'Ringkasan operator',
        $isOperator && request()->routeIs('operator.upt.*', 'operator.upt.registri') => 'Data UPT wilayah',
        $isOperator && request()->routeIs('operator.requests.create') => 'Ajukan draf usulan',
        $isOperator && request()->routeIs('operator.requests.show') => 'Rincian draf usulan',
        $isOperator && request()->routeIs('operator.requests.*') => 'Draf usulan',
        $isOperator && request()->routeIs('operator.documents.*') => 'Arsip dokumen',
        request()->routeIs('admin.dashboard') => 'Ringkasan pekerjaan',
        request()->routeIs('admin.verification.index') => 'Verifikasi',
        request()->routeIs('admin.verification.show') => 'Periksa perubahan',
        request()->routeIs('admin.upt.index') => 'Data UPT',
        request()->routeIs('admin.upt.create') => 'Tambah UPT',
        request()->routeIs('admin.upt.edit') => 'Perbarui data UPT',
        request()->routeIs('admin.upt.show') => 'Profil UPT',
        $populationPage => 'Kependudukan',
        request()->routeIs('admin.sertifikat-tanah.*') => 'Pertanahan',
        request()->routeIs('admin.documents.*') => 'Arsip dokumen',
        request()->routeIs('admin.analitik.*') => 'Analitik & laporan',
        request()->routeIs('admin.users.*') => 'Pengguna',
        request()->routeIs('admin.regencies.*') => 'Wilayah publikasi',
        request()->routeIs('admin.audit-logs.*') => 'Log aktivitas',
        request()->routeIs('admin.backup.*') => 'Cadangan data',
        request()->routeIs('profile.*') => 'Profil saya',
        default => 'Ruang kerja',
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageName }} — SIGAP-TRANS KALSEL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/atlas.css', 'resources/js/app.js'])
</head>
<body class="atlas-shell" x-data="atlasShell" :class="{ 'atlas-collapsed': collapsed, 'atlas-menu-open': menuOpen }">
    <a href="#atlas-content" class="atlas-skip">Lewati ke konten</a>
    <button class="atlas-backdrop" x-show="menuOpen" x-cloak @click="closeMenu()" aria-label="Tutup navigasi" tabindex="-1"></button>
    <aside class="atlas-sidebar" id="atlas-navigation" x-ref="sidebar" @keydown.escape="closeMenu()" @keydown.tab="trapMenu($event)" :inert="mobile && !menuOpen">
        <a class="atlas-brand" href="{{ route($homeRoute) }}" title="SIGAP-TRANS · Ringkasan">
            <span class="atlas-brand-mark"><x-admin.icon name="map" /></span>
            <span class="atlas-nav-label"><strong>SIGAP-TRANS</strong><small>Kalimantan Selatan</small></span>
        </a>
        <button class="atlas-mobile-close atlas-icon-button" @click="closeMenu()" aria-label="Tutup navigasi"><x-admin.icon name="close" /></button>
        <nav aria-label="Navigasi Ruang Kerja" class="atlas-nav">
            @if($isOperator)
                <p class="atlas-nav-heading">Ruang kerja</p>
                <x-admin.nav-item route="operator.dashboard" label="Ringkasan" icon="overview" :active="request()->routeIs('operator.dashboard')" />
                <p class="atlas-nav-heading">Data wilayah</p>
                <x-admin.nav-item route="operator.upt.index" label="Data UPT wilayah" icon="database" :active="request()->routeIs('operator.upt.*')" />
                <x-admin.nav-item route="operator.documents.index" label="Arsip dokumen" icon="folder" :active="request()->routeIs('operator.documents.*')" />
                <p class="atlas-nav-heading">Pelaporan</p>
                <x-admin.nav-item route="operator.requests.index" label="Draf usulan" icon="check" :badge="$user->pendingRequestsCount()" :active="request()->routeIs('operator.requests.*')" />
            @else
            <p class="atlas-nav-heading">Ruang kerja</p>
            <x-admin.nav-item route="admin.dashboard" label="Ringkasan" icon="overview" :active="request()->routeIs('admin.dashboard')" />
            <x-admin.nav-item route="admin.verification.index" :parameters="['status' => 'pending']" label="Verifikasi" icon="check" :badge="$pendingCount" :active="request()->routeIs('admin.verification.*')" />
            <p class="atlas-nav-heading">Data wilayah</p>
            <x-admin.nav-item route="admin.upt.index" label="Data UPT" icon="database" :active="request()->routeIs('admin.upt.*')" />
            <x-admin.nav-item route="admin.placements.index" label="Kependudukan" icon="users" :active="$populationPage" />
            <x-admin.nav-item route="admin.sertifikat-tanah.index" label="Pertanahan" icon="land" :active="request()->routeIs('admin.sertifikat-tanah.*')" />
            <x-admin.nav-item route="admin.documents.index" label="Arsip dokumen" icon="folder" :active="request()->routeIs('admin.documents.*')" />
            <p class="atlas-nav-heading">Pelaporan</p>
            <x-admin.nav-item route="admin.analitik.index" label="Analitik & laporan" icon="chart" :active="request()->routeIs('admin.analitik.*')" />
            @if($user && $user->role === 'super_admin')
                <details class="atlas-admin-group" @if($administrationPage) open @endif>
                    <summary class="atlas-nav-heading" title="Administrasi"><span>Administrasi</span><x-admin.icon name="chevron" /></summary>
                    <x-admin.nav-item route="admin.users.index" label="Pengguna" icon="users" :active="request()->routeIs('admin.users.*')" />
                    <x-admin.nav-item route="admin.regencies.index" label="Wilayah publikasi" icon="map" :active="request()->routeIs('admin.regencies.*')" />
                    <x-admin.nav-item route="admin.audit-logs.index" label="Log aktivitas" icon="history" :active="request()->routeIs('admin.audit-logs.*')" />
                    <x-admin.nav-item route="admin.backup.index" label="Cadangan data" icon="database" :active="request()->routeIs('admin.backup.*')" />
                </details>
            @endif
            @endif
        </nav>
        <div class="atlas-sidebar-bottom">
            <a class="atlas-nav-item" href="{{ route('home') }}" target="_blank" rel="noopener" title="Buka peta publik"><x-admin.icon name="external" /><span class="atlas-nav-label">Buka peta publik</span></a>
            <details class="atlas-account" @click.outside="$el.removeAttribute('open')" @keydown.escape.stop="$el.removeAttribute('open')">
                <summary title="Menu akun"><span class="atlas-avatar">{{ mb_strtoupper(mb_substr($user?->name ?? 'A', 0, 1)) }}</span><span class="atlas-nav-label"><strong>{{ $user?->name ?? 'Pengguna' }}</strong><small>{{ match($user?->role) { 'super_admin' => 'Super Admin · Provinsi', 'operator_kabupaten' => 'Operator · ' . ($user?->regency?->name ?? 'Wilayah'), 'eksekutif' => 'Eksekutif', 'mitra_bpn' => 'Mitra BPN', default => 'Aparatur' } }}</small></span></summary>
                <div class="atlas-account-menu"><a href="{{ route('profile.edit') }}">Profil & kata sandi</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-admin.icon name="logout" /> Keluar</button></form></div>
            </details>
        </div>
    </aside>
    <div class="atlas-workspace" :inert="menuOpen && mobile">
        <header class="atlas-topbar">
            <div class="atlas-inline">
                <button class="atlas-icon-button" x-ref="menuButton" @click="toggleNavigation()" :aria-expanded="mobile ? menuOpen : !collapsed" aria-controls="atlas-navigation" aria-label="Buka atau ringkas navigasi"><x-admin.icon name="collapse" /></button>
                <nav aria-label="Breadcrumb" class="atlas-breadcrumb"><a href="{{ route($homeRoute) }}">Ruang kerja</a><span aria-hidden="true">/</span><span aria-current="page">{{ $pageName }}</span></nav>
            </div>
            <span class="atlas-office">Disnakertrans Prov. Kalsel</span>
        </header>
        <main id="atlas-content" class="atlas-content" tabindex="-1">
            <div class="atlas-page-heading">
                <div><p class="atlas-eyebrow">Administrasi transmigrasi</p><h1>@yield('atlas_title', $pageName)</h1><p class="atlas-muted">@yield('atlas_subtitle', 'Kelola data wilayah dan tindak lanjut administrasi Kalimantan Selatan.')</p></div>
                <div class="atlas-actions">@yield('atlas_actions')<x-admin.export /></div>
            </div>
            @if($populationPage)
                <nav class="atlas-tabs" aria-label="Tahap kependudukan">
                    <a href="{{ route('admin.placements.index', request()->only('regency_id', 'search')) }}" @if(request()->routeIs('admin.placements.*')) aria-current="page" @endif>Penempatan awal</a>
                    <a href="{{ route('admin.handovers.index', request()->only('regency_id', 'search')) }}" @if(request()->routeIs('admin.handovers.*')) aria-current="page" @endif>Serah terima</a>
                </nav>
            @endif
            @foreach(['success', 'error'] as $messageType)
                @if(session($messageType))<div class="atlas-alert atlas-alert--{{ $messageType }}" role="{{ $messageType === 'error' ? 'alert' : 'status' }}">{{ session($messageType) }}</div>@endif
            @endforeach
            @if($errors->any())<div class="atlas-alert atlas-alert--error" role="alert"><strong>Periksa kembali isian berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="atlas-page-body">@yield('content')</div>
        </main>
        <footer class="atlas-footer"><span>SIGAP-TRANS KALSEL</span><span>Data wilayah · Administrasi · Arsip</span></footer>
    </div>
    @stack('scripts')
</body>
</html>
