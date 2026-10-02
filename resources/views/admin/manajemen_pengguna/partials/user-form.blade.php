{{-- Partial form akun pengguna bersama (create & edit) versi Atlas.
    Diharapkan: $user (null saat create), $regencies. --}}
@php
    $isEdit = isset($user) && $user !== null;
    $old = fn (string $key, $default = null) => old($key, $isEdit ? $user->{$key} : $default);
    $roleLabels = ['operator_kabupaten' => 'Operator wilayah kabupaten', 'super_admin' => 'Super Admin (provinsi)', 'eksekutif' => 'Eksekutif (Kadis/Kabid)', 'mitra_bpn' => 'Mitra Kanwil ATR/BPN'];
@endphp
<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Identitas aparatur</h2></div>
    <div class="atlas-panel-body atlas-form-grid">
        <div class="atlas-field">
            <label for="name">Nama lengkap beserta gelar <span aria-hidden="true">*</span></label>
            <input type="text" name="name" id="name" required maxlength="150" value="{{ $old('name') }}" placeholder="Contoh: Muhammad Rifani, S.AP">
        </div>
        <div class="atlas-field">
            <label for="nip">Nomor Induk Pegawai (NIP)</label>
            <input type="text" name="nip" id="nip" maxlength="30" value="{{ $old('nip') }}" placeholder="Contoh: 198506152010011012">
        </div>
        <div class="atlas-field">
            <label for="email">Email dinas <span aria-hidden="true">*</span></label>
            <input type="email" name="email" id="email" required value="{{ $old('email') }}" placeholder="Contoh: operator.batola@kalselprov.go.id">
        </div>
        <div class="atlas-field">
            <label for="password">{{ $isEdit ? 'Kata sandi baru (kosongkan bila tidak diubah)' : 'Kata sandi awal' }} @unless($isEdit)<span aria-hidden="true">*</span>@endunless</label>
            <input type="password" name="password" id="password" @unless($isEdit) required @endunless placeholder="Minimal 6 karakter">
        </div>
        <div class="atlas-field">
            <label for="position">Jabatan kedinasan</label>
            <input type="text" name="position" id="position" maxlength="150" value="{{ $old('position') }}" placeholder="Contoh: Staf Bidang Transmigrasi">
        </div>
        <div class="atlas-field">
            <label for="phone">Nomor kontak (HP/WA)</label>
            <input type="text" name="phone" id="phone" maxlength="25" value="{{ $old('phone') }}" placeholder="Contoh: 081234567890">
        </div>
    </div>
</section>

<section class="atlas-panel">
    <div class="atlas-panel-heading"><h2>Hak akses & penugasan</h2></div>
    <div class="atlas-panel-body atlas-form-grid">
        <div class="atlas-field">
            <label for="role">Peran <span aria-hidden="true">*</span></label>
            <select name="role" id="role" required onchange="toggleUserRegency(this.value)">
                @foreach($roleLabels as $value => $label)
                    <option value="{{ $value }}" @selected($old('role', $isEdit ? null : 'operator_kabupaten') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="atlas-field" id="user-regency-field">
            <label for="regency_id">Wilayah kabupaten tugas <span aria-hidden="true">*</span></label>
            <select name="regency_id" id="regency_id">
                <option value="">— Pilih kabupaten —</option>
                @foreach($regencies as $reg)
                    <option value="{{ $reg->id }}" @selected($old('regency_id') == $reg->id)>{{ $reg->name }}</option>
                @endforeach
            </select>
            <p class="atlas-form-hint">Wajib untuk peran Operator wilayah; peran lain berkapasitas provinsi.</p>
        </div>
        <div class="atlas-field atlas-field--wide">
            <label class="atlas-form-hint" style="display: flex; align-items: center; gap: 9px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $isEdit ? $user->is_active : '1') ? 'checked' : '' }} style="width: 16px; height: 16px;">
                <span style="font-size: 14px; color: var(--atlas-ink);">Akun aktif dan dapat masuk ke sistem</span>
            </label>
        </div>
    </div>
</section>

@push('scripts')
<script>
function toggleUserRegency(role) {
    const field = document.getElementById('user-regency-field');
    if (!field) return;
    const select = document.getElementById('regency_id');
    const required = role === 'operator_kabupaten';
    field.style.display = required ? '' : 'none';
    if (select) select.required = required;
}
document.addEventListener('DOMContentLoaded', () => {
    const role = document.getElementById('role');
    if (role) toggleUserRegency(role.value);
});
</script>
@endpush
