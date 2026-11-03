{{-- Modal Form Input Desa 1 per 1 (Atlas Style) --}}
<style>
.atlas-modal-backdrop {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(14, 42, 71, 0.5) !important;
    backdrop-filter: blur(2px) !important;
    z-index: 10000 !important;
    overflow-y: auto !important;
    padding: 24px 16px !important;
    box-sizing: border-box !important;
}
.atlas-modal {
    position: relative !important;
    background: #ffffff !important;
    border-radius: 8px !important;
    box-shadow: 0 20px 45px rgba(14, 42, 71, 0.25) !important;
    overflow: hidden !important;
    animation: atlasModalIn 0.2s ease-out !important;
    margin: 40px auto !important;
}
@keyframes atlasModalIn {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}
#modal-village-form .atlas-field {
    min-width: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}
#modal-village-form .atlas-field input,
#modal-village-form .atlas-field select {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    box-sizing: border-box !important;
}
</style>

<div id="modal-village-form" class="atlas-modal-backdrop" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="mvf-title">
    <div class="atlas-modal" style="max-width: 580px; margin: 30px auto;">
        <div class="atlas-modal-header" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid var(--atlas-line, #D4DEE7);">
            <div>
                <h3 id="mvf-title" style="margin: 0; font-size: 18px; font-weight: 700; color: #0E2A47;">Tambah Data Desa</h3>
                <p style="margin: 2px 0 0; font-size: 12px; color: #5A6E7D;">Input data desa, luas wilayah, dan rincian kependudukan</p>
            </div>
            <button type="button" class="atlas-icon-button" onclick="closeVillageFormModal()" aria-label="Tutup form" style="cursor: pointer;">
                <x-admin.icon name="close" />
            </button>
        </div>

        <div id="form-single-village-box">
            <input type="hidden" id="mvf-id" value="">
            <input type="hidden" id="mvf-row-index" value="">

            <div class="atlas-modal-body" style="padding: 20px 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; width: 100%; box-sizing: border-box;">
                    <div class="atlas-field">
                        <label for="mvf-district">Kecamatan <span aria-hidden="true">*</span></label>
                        <input type="text" id="mvf-district" required placeholder="Contoh: Mandastana" style="font-size: 13px;">
                    </div>
                    <div class="atlas-field">
                        <label for="mvf-village">Nama Desa / Kelurahan <span aria-hidden="true">*</span></label>
                        <input type="text" id="mvf-village" required placeholder="Contoh: Bangkit Baru" style="font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; width: 100%; box-sizing: border-box;">
                    <div class="atlas-field">
                        <label for="mvf-area">Luas Wilayah (Km²)</label>
                        <input type="number" step="0.01" min="0" id="mvf-area" placeholder="Contoh: 12.00" class="tabular-nums" style="font-size: 13px;">
                    </div>
                    <div class="atlas-field">
                        <label for="mvf-family">Jumlah Kepala Keluarga (KK)</label>
                        <input type="number" min="0" id="mvf-family" placeholder="Contoh: 244" class="tabular-nums" style="font-size: 13px;">
                    </div>
                </div>

                {{-- Demografi L vs P dan Total Jiwa --}}
                <div style="background: #F8FAFC; border: 1px solid var(--atlas-line, #D4DEE7); border-radius: 6px; padding: 14px; width: 100%; box-sizing: border-box;">
                    <span style="display: block; font-size: 12px; font-weight: 700; color: #0E2A47; margin-bottom: 8px;">Rincian Demografi Penduduk</span>
                    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; width: 100%; box-sizing: border-box; align-items: start;">
                        <div class="atlas-field">
                            <label for="mvf-male" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Penduduk Laki-laki (L)">Penduduk Laki-laki (L)</label>
                            <input type="number" min="0" id="mvf-male" placeholder="0" class="tabular-nums" style="font-size: 13px;" oninput="updateMvfTotal()" onkeyup="updateMvfTotal()" onchange="updateMvfTotal()">
                        </div>
                        <div class="atlas-field">
                            <label for="mvf-female" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Penduduk Perempuan (P)">Penduduk Perempuan (P)</label>
                            <input type="number" min="0" id="mvf-female" placeholder="0" class="tabular-nums" style="font-size: 13px;" oninput="updateMvfTotal()" onkeyup="updateMvfTotal()" onchange="updateMvfTotal()">
                        </div>
                        <div class="atlas-field">
                            <label for="mvf-total" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Total Jiwa (L+P)">Total Jiwa (L+P)</label>
                            <input type="text" id="mvf-total" readonly placeholder="0" class="tabular-nums" style="font-size: 13px; background: #E2E8F0; border: 1px solid #CBD5E1; font-weight: 700; color: #0E2A47; text-align: center; cursor: not-allowed; min-height: 39px; border-radius: 5px;">
                        </div>
                    </div>
                    <span class="atlas-form-hint" style="margin-top: 6px; display: block; font-size: 11px;">Total jiwa otomatis terhitung dari L + P.</span>
                </div>

                {{-- Kaitkan ke UPT --}}
                <div class="atlas-field">
                    <label for="mvf-upt">Kaitkan ke Satuan Permukiman UPT (Opsional)</label>
                    <select id="mvf-upt" style="font-size: 13px;">
                        <option value="">— Bukan UPT / Desa Mandiri —</option>
                        @isset($uptLocations)
                            @foreach($uptLocations as $upt)
                                <option value="{{ $upt->id }}">{{ $upt->upt_name }} ({{ $upt->current_village_name }})</option>
                            @endforeach
                        @endisset
                    </select>
                    <span class="atlas-form-hint">Pilih jika desa ini merupakan desa definitif dari salah satu UPT binaan.</span>
                </div>
            </div>

            <div class="atlas-modal-footer" style="padding: 14px 24px; border-top: 1px solid var(--atlas-line, #D4DEE7); display: flex; justify-content: flex-end; gap: 10px; background-color: #F8FAFC;">
                <button type="button" class="atlas-button" onclick="closeVillageFormModal()">Batal</button>
                <button type="button" id="mvf-submit-btn" class="atlas-button atlas-button--primary" onclick="handleVillageFormSubmit(event)">
                    Simpan Data Desa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateMvfTotal() {
    const male = parseInt(document.getElementById('mvf-male').value, 10) || 0;
    const female = parseInt(document.getElementById('mvf-female').value, 10) || 0;
    const total = male + female;
    const totalEl = document.getElementById('mvf-total');
    if (totalEl) {
        totalEl.value = total > 0 ? total : 0;
    }
}

function openAddVillageModal() {
    document.getElementById('mvf-title').textContent = 'Tambah Data Desa ke SKP';
    document.getElementById('mvf-id').value = '';
    document.getElementById('mvf-row-index').value = '';
    document.getElementById('mvf-district').value = '';
    document.getElementById('mvf-village').value = '';
    document.getElementById('mvf-area').value = '';
    document.getElementById('mvf-male').value = '';
    document.getElementById('mvf-female').value = '';
    document.getElementById('mvf-total').value = '0';
    document.getElementById('mvf-family').value = '';
    document.getElementById('mvf-upt').selectedIndex = 0;

    const modal = document.getElementById('modal-village-form');
    if (modal) modal.style.display = 'block';
    setTimeout(() => {
        const distEl = document.getElementById('mvf-district');
        if (distEl) distEl.focus();
    }, 50);
}

function openEditVillageModal(rowEl) {
    const row = rowEl.closest('tr');
    if (!row) return;

    document.getElementById('mvf-title').textContent = 'Ubah Data Desa';
    document.getElementById('mvf-id').value = row.dataset.id || '';
    document.getElementById('mvf-row-index').value = row.dataset.index || '';
    document.getElementById('mvf-district').value = row.dataset.district || '';
    document.getElementById('mvf-village').value = row.dataset.village || '';
    document.getElementById('mvf-area').value = row.dataset.area || '';
    document.getElementById('mvf-male').value = row.dataset.male || '';
    document.getElementById('mvf-female').value = row.dataset.female || '';
    const maleVal = parseInt(row.dataset.male, 10) || 0;
    const femaleVal = parseInt(row.dataset.female, 10) || 0;
    document.getElementById('mvf-total').value = maleVal + femaleVal;
    document.getElementById('mvf-family').value = row.dataset.family || '';

    const uptSelect = document.getElementById('mvf-upt');
    if (uptSelect) {
        uptSelect.value = row.dataset.uptId || '';
    }

    const modal = document.getElementById('modal-village-form');
    if (modal) modal.style.display = 'block';
}

function closeVillageFormModal() {
    const modal = document.getElementById('modal-village-form');
    if (modal) modal.style.display = 'none';
}

async function handleVillageFormSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();

    const id = document.getElementById('mvf-id').value;
    const rowIndex = document.getElementById('mvf-row-index').value;
    const district = document.getElementById('mvf-district').value.trim();
    const village = document.getElementById('mvf-village').value.trim();
    const area = parseFloat(document.getElementById('mvf-area').value) || 0;
    const male = parseInt(document.getElementById('mvf-male').value, 10) || 0;
    const female = parseInt(document.getElementById('mvf-female').value, 10) || 0;
    const total = male + female;
    const family = parseInt(document.getElementById('mvf-family').value, 10) || 0;
    const uptSelect = document.getElementById('mvf-upt');
    const uptId = uptSelect ? (uptSelect.value || '') : '';
    const uptText = uptId ? uptSelect.options[uptSelect.selectedIndex].text : 'Non-UPT';

    if (!district) {
        if (window.showToast) window.showToast('Nama kecamatan wajib diisi.', 'error');
        else alert('Nama kecamatan wajib diisi');
        document.getElementById('mvf-district').focus();
        return;
    }
    if (!village) {
        if (window.showToast) window.showToast('Nama desa wajib diisi.', 'error');
        else alert('Nama desa wajib diisi');
        document.getElementById('mvf-village').focus();
        return;
    }

    const isEdit = Boolean(id || (rowIndex !== '' && rowIndex !== null && rowIndex !== undefined));
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: isEdit ? 'Konfirmasi Perubahan Data Desa' : 'Konfirmasi Tambah Desa',
            message: isEdit 
                ? `Simpan pembaruan data desa '${village}' pada kawasan SKP ini?` 
                : `Tambahkan data desa '${village}' ke dalam kawasan SKP ini?`,
            type: isEdit ? 'warning' : 'primary',
            confirmText: isEdit ? 'Ya, Simpan Perubahan' : 'Ya, Tambahkan Desa'
        });
        if (!ok) return;
    }

    const skpId = window.currentSkpId || null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (skpId && csrfToken) {
        const submitBtn = document.getElementById('mvf-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan…';

        try {
            const url = id ? `/admin/skp/villages/${id}` : `/admin/skp/${skpId}/villages`;
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    district_name: district,
                    village_name: village,
                    area_km2: area,
                    population_male: male,
                    population_female: female,
                    family_count: family,
                    upt_location_id: uptId || null,
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Gagal menyimpan data desa');

            updateOrInsertVillageRow({
                id: data.village.id,
                district: district,
                village: village,
                area: area,
                male: male,
                female: female,
                total: total,
                family: family,
                uptId: uptId,
                uptText: uptText
            });

            closeVillageFormModal();
            if (window.showToast) {
                window.showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
        } catch (err) {
            if (window.showToast) {
                window.showToast(err.message || 'Terjadi kesalahan saat menyimpan desa', 'error');
            } else {
                alert(err.message || 'Terjadi kesalahan saat menyimpan desa');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Data Desa';
        }
        return;
    }

    // Client-side row insertion for Create form
    updateOrInsertVillageRowClientSide({
        rowIndex: rowIndex,
        district: district,
        village: village,
        area: area,
        male: male,
        female: female,
        total: total,
        family: family,
        uptId: uptId,
        uptText: uptText
    });

    closeVillageFormModal();
    if (window.showToast) {
        window.showToast(isEdit ? `Data desa '${village}' berhasil diperbarui.` : `Data desa '${village}' berhasil ditambahkan ke daftar.`, 'success');
    }
}

function updateOrInsertVillageRowClientSide(data) {
    const tbody = document.getElementById('village-clean-tbody');
    const emptyRow = document.getElementById('empty-village-row');
    if (emptyRow) emptyRow.remove();

    let tr = null;
    let idx = data.rowIndex;

    if (idx !== '' && idx !== null && idx !== undefined) {
        tr = tbody.querySelector(`tr[data-index="${idx}"]`);
    }

    if (!tr) {
        idx = window.nextClientRowIndex || tbody.children.length;
        window.nextClientRowIndex = idx + 1;
        tr = document.createElement('tr');
        tr.dataset.index = idx;
        tbody.appendChild(tr);
    }

    tr.dataset.district = data.district;
    tr.dataset.village = data.village;
    tr.dataset.area = data.area;
    tr.dataset.male = data.male;
    tr.dataset.female = data.female;
    tr.dataset.total = data.total;
    tr.dataset.family = data.family;
    tr.dataset.uptId = data.uptId;

    tr.innerHTML = `
        <input type="hidden" name="villages[${idx}][district_name]" value="${data.district}">
        <input type="hidden" name="villages[${idx}][village_name]" value="${data.village}">
        <input type="hidden" name="villages[${idx}][area_km2]" value="${data.area}">
        <input type="hidden" name="villages[${idx}][population_male]" value="${data.male}">
        <input type="hidden" name="villages[${idx}][population_female]" value="${data.female}">
        <input type="hidden" name="villages[${idx}][family_count]" value="${data.family}">
        <input type="hidden" name="villages[${idx}][upt_location_id]" value="${data.uptId}">

        <td>
            <strong>${data.village}</strong>
            <span class="atlas-muted" style="display: block; font-size: 11px;">Kec. ${data.district}</span>
        </td>
        <td class="tabular-nums" style="text-align: right;">${data.area.toLocaleString('id-ID', {minimumFractionDigits: 2})} Km²</td>
        <td class="tabular-nums" style="text-align: right;">${data.male.toLocaleString('id-ID')} L · ${data.female.toLocaleString('id-ID')} P</td>
        <td class="tabular-nums" style="text-align: right; font-weight: 700; color: #0E2A47;">${data.total.toLocaleString('id-ID')} Jiwa</td>
        <td class="tabular-nums" style="text-align: right; font-weight: 600; color: #107C41;">${data.family.toLocaleString('id-ID')} KK</td>
        <td>
            <span class="atlas-badge" style="font-size: 11px; ${data.uptId ? 'background: #EFF6FF; color: #1E40AF;' : 'background: #F1F5F9; color: #64748B;'}">
                ${data.uptText}
            </span>
        </td>
        <td style="text-align: center;">
            <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                <button type="button" class="atlas-action-icon atlas-action-icon--edit" onclick="openEditVillageModal(this)" title="Ubah Data Desa" aria-label="Ubah">
                    <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="atlas-action-icon atlas-action-icon--delete" onclick="deleteClientSideVillageRow(this)" title="Hapus Data Desa" aria-label="Hapus">
                    <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2m-6 5v6m4-6v6"/></svg>
                </button>
            </div>
        </td>
    `;

    recalcVillageSummary();
}

function updateOrInsertVillageRow(data) {
    const tbody = document.getElementById('village-clean-tbody');
    const emptyRow = document.getElementById('empty-village-row');
    if (emptyRow) emptyRow.remove();

    let tr = tbody.querySelector(`tr[data-id="${data.id}"]`);
    if (!tr) {
        tr = document.createElement('tr');
        tr.dataset.id = data.id;
        tbody.appendChild(tr);
    }

    tr.dataset.district = data.district;
    tr.dataset.village = data.village;
    tr.dataset.area = data.area;
    tr.dataset.male = data.male;
    tr.dataset.female = data.female;
    tr.dataset.total = data.total;
    tr.dataset.family = data.family;
    tr.dataset.uptId = data.uptId;

    tr.innerHTML = `
        <td>
            <strong>${data.village}</strong>
            <span class="atlas-muted" style="display: block; font-size: 11px;">Kec. ${data.district}</span>
        </td>
        <td class="tabular-nums" style="text-align: right;">${data.area.toLocaleString('id-ID', {minimumFractionDigits: 2})} Km²</td>
        <td class="tabular-nums" style="text-align: right;">${data.male.toLocaleString('id-ID')} L · ${data.female.toLocaleString('id-ID')} P</td>
        <td class="tabular-nums" style="text-align: right; font-weight: 700; color: #0E2A47;">${data.total.toLocaleString('id-ID')} Jiwa</td>
        <td class="tabular-nums" style="text-align: right; font-weight: 600; color: #107C41;">${data.family.toLocaleString('id-ID')} KK</td>
        <td>
            <span class="atlas-badge" style="font-size: 11px; ${data.uptId ? 'background: #EFF6FF; color: #1E40AF;' : 'background: #F1F5F9; color: #64748B;'}">
                ${data.uptText}
            </span>
        </td>
        <td style="text-align: center;">
            <div class="atlas-actions-cell" style="justify-content: center; gap: 6px;">
                <button type="button" class="atlas-action-icon atlas-action-icon--edit" onclick="openEditVillageModal(this)" title="Ubah Data Desa" aria-label="Ubah">
                    <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button type="button" class="atlas-action-icon atlas-action-icon--delete" onclick="deleteServerVillageRow(this, ${data.id})" title="Hapus Data Desa" aria-label="Hapus">
                    <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2m-6 5v6m4-6v6"/></svg>
                </button>
            </div>
        </td>
    `;

    recalcVillageSummary();
}

async function deleteClientSideVillageRow(btn) {
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Hapus Data Desa',
            message: 'Hapus data baris desa ini dari daftar kawasan?',
            type: 'danger',
            confirmText: 'Ya, Hapus'
        });
        if (!ok) return;
    } else if (!confirm('Hapus data desa ini dari daftar?')) return;
    const row = btn.closest('tr');
    row.remove();
    recalcVillageSummary();
}

async function deleteServerVillageRow(btn, id) {
    if (window.confirmAtlas) {
        const ok = await window.confirmAtlas({
            title: 'Hapus Data Desa',
            message: 'Hapus data desa ini secara permanen dari basis data kawasan?',
            type: 'danger',
            confirmText: 'Ya, Hapus Permanen'
        });
        if (!ok) return;
    } else if (!confirm('Hapus data desa ini secara permanen dari kawasan?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(`/admin/skp/villages/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Gagal menghapus desa');
        const row = btn.closest('tr');
        row.remove();
        recalcVillageSummary();
        if (window.showToast) {
            window.showToast(data.message, 'success');
        } else {
            alert(data.message);
        }
    } catch (err) {
        if (window.showToast) {
            window.showToast(err.message || 'Terjadi kesalahan saat menghapus desa', 'error');
        } else {
            alert(err.message || 'Terjadi kesalahan saat menghapus desa');
        }
    }
}

function recalcVillageSummary() {
    const rows = document.querySelectorAll('#village-clean-tbody tr:not(#empty-village-row)');
    let count = rows.length;
    let totalArea = 0;
    let totalPop = 0;
    let totalFamily = 0;

    rows.forEach(r => {
        totalArea += parseFloat(r.dataset.area) || 0;
        totalPop += (parseInt(r.dataset.male, 10) || 0) + (parseInt(r.dataset.female, 10) || 0);
        totalFamily += parseInt(r.dataset.family, 10) || 0;
    });

    const sumCountEl = document.getElementById('summary-village-count');
    if (sumCountEl) sumCountEl.textContent = `${count} Desa`;

    const sumAreaEl = document.getElementById('summary-total-area');
    if (sumAreaEl) sumAreaEl.textContent = `${totalArea.toLocaleString('id-ID', {minimumFractionDigits: 2})} Km²`;

    const sumPopEl = document.getElementById('summary-total-pop');
    if (sumPopEl) sumPopEl.textContent = `${totalPop.toLocaleString('id-ID')} Jiwa`;

    const sumFamEl = document.getElementById('summary-total-fam');
    if (sumFamEl) sumFamEl.textContent = `${totalFamily.toLocaleString('id-ID')} KK`;
}
</script>
