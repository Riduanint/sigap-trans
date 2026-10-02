@extends('layouts.admin')
@section('atlas_subtitle', 'Akun aparatur, peran akses, dan wilayah penugasan di seluruh kabupaten.')
@section('atlas_actions')<a class="atlas-button atlas-button--primary" href="{{ route('admin.users.create') }}"><x-admin.icon name="plus" /> Tambah akun</a>@endsection
@section('content')
@php
    $roleLabels = ['super_admin' => 'Super Admin', 'operator_kabupaten' => 'Operator wilayah', 'eksekutif' => 'Eksekutif', 'mitra_bpn' => 'Mitra ATR/BPN'];
    $activeFilters = collect(request()->only('role', 'regency_id', 'search'))->filter(fn ($value) => filled($value));
@endphp
<div class="atlas-metrics" aria-label="Ringkasan pengguna">
    <div class="atlas-metric"><div><strong>{{ $counts['total'] }}</strong><small>Total akun terdaftar</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $counts['super_admin'] }}</strong><small>Super Admin · otoritas provinsi</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $counts['operator'] }}</strong><small>Operator wilayah kabupaten</small></div></div>
    <div class="atlas-metric"><div><strong>{{ $counts['eksekutif'] + $counts['bpn'] }}</strong><small>Eksekutif & mitra BPN</small></div></div>
</div>

<section class="atlas-panel">
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="atlas-filter">
            <div class="atlas-field atlas-field--search"><label for="us-search">Cari pengguna</label><input id="us-search" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau NIP" type="search"></div>
            <div class="atlas-field"><label for="us-role">Peran</label><select id="us-role" name="role"><option value="">Semua peran</option>@foreach($roleLabels as $value => $label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="atlas-field"><label for="us-regency">Wilayah tugas</label><select id="us-regency" name="regency_id"><option value="">Semua wilayah</option>@foreach($regencies as $regency)<option value="{{ $regency->id }}" @selected(request('regency_id') == $regency->id)>{{ $regency->name }}</option>@endforeach</select></div>
            <button class="atlas-button atlas-button--primary">Terapkan</button>
        </div>
    </form>
    @if($activeFilters->isNotEmpty())
        <div class="atlas-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.users.index', request()->except([$key, 'page'])) }}" aria-label="Hapus filter {{ $key }}">{{ $key === 'role' ? ($roleLabels[$value] ?? $value) : ($key === 'regency_id' ? $regencies->firstWhere('id', $value)?->name : $value) }} <span aria-hidden="true">×</span></a>@endforeach<a href="{{ route('admin.users.index') }}">Hapus semua filter</a></div>
    @endif
    <div class="atlas-panel-heading"><h2>{{ number_format($users->total(), 0, ',', '.') }} akun ditemukan</h2><span class="atlas-muted">Nonaktifkan daripada hapus bila akun masih bersejarah di log</span></div>
    <div class="atlas-table-scroll"><table class="atlas-table"><thead><tr><th scope="col">Nama & jabatan</th><th scope="col">Email & NIP</th><th scope="col">Peran</th><th scope="col">Wilayah tugas</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
        @forelse($users as $u)
            <tr>
                <td><div class="atlas-identity"><strong>{{ $u->name }}</strong><span class="atlas-muted">{{ $u->position ?: 'Jabatan belum tercatat' }}</span></div></td>
                <td>{{ $u->email }}<br><span class="atlas-muted">NIP: {{ $u->nip ?: 'Belum tercatat' }}</span></td>
                <td>{{ $roleLabels[$u->role] ?? $u->role }}</td>
                <td>{{ $u->regency?->name ?: 'Pemprov Kalsel' }}</td>
                <td><span class="atlas-status atlas-status--{{ $u->is_active ? 'clean' : 'neutral' }}"><span aria-hidden="true"></span>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>
                    <div class="atlas-inline">
                        <a class="atlas-link" href="{{ route('admin.users.edit', $u->id) }}">Edit</a>
                        @if($u->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $u->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="atlas-link">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun {{ $u->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="atlas-link" style="color: var(--atlas-critical);">Hapus</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="atlas-empty"><strong>Tidak ada akun yang cocok.</strong><p>Ubah kata kunci atau hapus sebagian filter.</p><a class="atlas-link" href="{{ route('admin.users.index') }}">Tampilkan seluruh akun →</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="atlas-table-footer"><span>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun</span>{{ $users->links('admin.partials.pagination') }}</div>
</section>
@endsection
