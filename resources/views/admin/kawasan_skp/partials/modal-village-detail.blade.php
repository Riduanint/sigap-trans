{{-- Modal Detail Profil Desa di Kawasan SKP (Atlas Style) --}}
<div id="modal-village-detail" class="atlas-modal-backdrop" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="mvd-title">
    <div class="atlas-modal" style="max-width: 540px; margin: 40px auto;">
        <div class="atlas-modal-header" style="display: flex; align-items: flex-start; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid var(--atlas-line, #D4DEE7);">
            <div>
                <span id="mvd-skp-badge" class="atlas-badge" style="background-color: #163A63; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; text-transform: uppercase;">SKP</span>
                <h3 id="mvd-title" style="margin: 8px 0 2px; font-size: 20px; font-weight: 700; color: #0E2A47;">Nama Desa</h3>
                <p id="mvd-subtitle" style="margin: 0; font-size: 13px; color: #5A6E7D;">Kecamatan · Kabupaten</p>
            </div>
            <button type="button" class="atlas-icon-button" onclick="closeVillageModal()" aria-label="Tutup dialog" style="cursor: pointer;">
                <x-admin.icon name="close" />
            </button>
        </div>

        <div class="atlas-modal-body" style="padding: 20px 24px;">
            <div id="mvd-loading" style="text-align: center; padding: 30px; color: #5A6E7D;">
                <span>Memuat data demografi desa…</span>
            </div>

            <div id="mvd-content" style="display: none; display: flex; flex-direction: column; gap: 16px;">
                {{-- Banner Afiliasi SKP --}}
                <div style="background-color: #F8FAFC; border: 1px solid var(--atlas-line, #D4DEE7); border-radius: 6px; padding: 12px 16px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #5A6E7D; font-weight: 600;">Bagian Kawasan Transmigrasi</span>
                    <strong id="mvd-kawasan" style="display: block; font-size: 14px; color: #0E2A47; margin-top: 2px;">Kawasan Transmigrasi Cahaya Baru</strong>
                    <span id="mvd-skp-detail" style="font-size: 12px; color: #5A6E7D;">Satuan Kawasan Pengembangan: <strong id="mvd-skp-name" style="color: #107C41;">-</strong></span>
                </div>

                {{-- Grid Statistik Demografi --}}
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    {{-- Luas Desa --}}
                    <div style="background: #fff; border: 1px solid var(--atlas-line, #D4DEE7); border-radius: 6px; padding: 12px;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #5A6E7D; font-weight: 600;">Luas Wilayah</span>
                        <div id="mvd-area" style="font-size: 18px; font-weight: 700; color: #0E2A47; margin-top: 4px;" class="tabular-nums">0 Km²</div>
                        <small style="color: #5A6E7D; font-size: 11px;">Luas terdata BPS / DPMD</small>
                    </div>

                    {{-- Jumlah KK --}}
                    <div style="background: #fff; border: 1px solid var(--atlas-line, #D4DEE7); border-radius: 6px; padding: 12px;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #5A6E7D; font-weight: 600;">Jumlah KK</span>
                        <div id="mvd-kk" style="font-size: 18px; font-weight: 700; color: #107C41; margin-top: 4px;" class="tabular-nums">0 KK</div>
                        <small style="color: #5A6E7D; font-size: 11px;">Kepala Keluarga</small>
                    </div>

                    {{-- Total Penduduk --}}
                    <div style="grid-column: span 2; background: #F4F7F9; border: 1px solid var(--atlas-line, #D4DEE7); border-radius: 6px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline;">
                            <span style="font-size: 12px; text-transform: uppercase; color: #5A6E7D; font-weight: 600;">Total Jumlah Penduduk</span>
                            <span id="mvd-pop-total" style="font-size: 22px; font-weight: 700; color: #0E2A47;" class="tabular-nums">0 Jiwa</span>
                        </div>

                        {{-- Rincian L vs P --}}
                        <div style="display: flex; gap: 16px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--atlas-line, #D4DEE7);">
                            <div style="flex: 1;">
                                <small style="display: block; font-size: 11px; color: #5A6E7D;">Laki-laki (L)</small>
                                <strong id="mvd-pop-male" style="font-size: 15px; color: #163A63;" class="tabular-nums">0</strong>
                            </div>
                            <div style="flex: 1;">
                                <small style="display: block; font-size: 11px; color: #5A6E7D;">Perempuan (P)</small>
                                <strong id="mvd-pop-female" style="font-size: 15px; color: #B43D3D;" class="tabular-nums">0</strong>
                            </div>
                            <div style="flex: 1;">
                                <small style="display: block; font-size: 11px; color: #5A6E7D;">Rasio Gender</small>
                                <span id="mvd-ratio" style="font-size: 13px; font-weight: 600; color: #243746;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Relasi UPT (jika ada) --}}
                <div id="mvd-upt-box" style="display: none; background: #FFF9E6; border: 1px solid #FFE699; border-radius: 6px; padding: 12px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #8A6D3B; font-weight: 700;">Terhubung ke Satuan Permukiman UPT</div>
                    <div id="mvd-upt-name" style="font-size: 13px; font-weight: 600; color: #333; margin-top: 2px;">UPT Asal: -</div>
                </div>
            </div>
        </div>

        <div class="atlas-modal-footer" style="padding: 14px 24px; border-top: 1px solid var(--atlas-line, #D4DEE7); display: flex; justify-content: flex-end; background-color: #F8FAFC;">
            <button type="button" class="atlas-button" onclick="closeVillageModal()">Tutup</button>
        </div>
    </div>
</div>

<style>
.atlas-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(14, 42, 71, 0.45);
    backdrop-filter: blur(2px);
    z-index: 1000;
    overflow-y: auto;
    padding: 16px;
}
.atlas-modal {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 16px 36px rgba(14, 42, 71, 0.2);
    overflow: hidden;
    animation: atlasModalIn 0.2s ease-out;
}
@keyframes atlasModalIn {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}
.village-chip-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #F1F5F9;
    color: #0E2A47;
    border: 1px solid #D4DEE7;
    border-radius: 4px;
    padding: 3px 8px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.village-chip-btn:hover {
    background: #0E2A47;
    color: #ffffff;
    border-color: #0E2A47;
}
</style>

<script>
async function openVillageModal(villageId) {
    const modal = document.getElementById('modal-village-detail');
    const loading = document.getElementById('mvd-loading');
    const content = document.getElementById('mvd-content');

    if (!modal) return;

    modal.style.display = 'block';
    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const response = await fetch(`/admin/skp/villages/${villageId}`);
        if (!response.ok) throw new Error('Gagal memuat profil desa');
        const data = await response.json();

        // Isi Data
        document.getElementById('mvd-title').textContent = data.village_name;
        document.getElementById('mvd-subtitle').textContent = `Kecamatan ${data.district_name} · Kab. ${data.skp.regency || '-'}`;
        document.getElementById('mvd-kawasan').textContent = data.skp.kawasan;
        document.getElementById('mvd-skp-name').textContent = data.skp.name;

        const badge = document.getElementById('mvd-skp-badge');
        badge.textContent = data.skp.name;
        badge.style.backgroundColor = data.skp.color || '#163A63';

        document.getElementById('mvd-area').textContent = data.area_formatted;
        document.getElementById('mvd-kk').textContent = data.family_count.toLocaleString('id-ID') + ' KK';
        document.getElementById('mvd-pop-total').textContent = data.population_total.toLocaleString('id-ID') + ' Jiwa';
        document.getElementById('mvd-pop-male').textContent = data.population_male.toLocaleString('id-ID') + ' L';
        document.getElementById('mvd-pop-female').textContent = data.population_female.toLocaleString('id-ID') + ' P';

        // Rasio Gender
        if (data.population_female > 0) {
            const ratio = ((data.population_male / data.population_female) * 100).toFixed(1);
            document.getElementById('mvd-ratio').textContent = `${ratio} (L/100P)`;
        } else {
            document.getElementById('mvd-ratio').textContent = '-';
        }

        // Relasi UPT
        const uptBox = document.getElementById('mvd-upt-box');
        if (data.upt) {
            uptBox.style.display = 'block';
            document.getElementById('mvd-upt-name').textContent = `UPT: ${data.upt.upt_name} (No. ${data.upt.upt_number}) · Pola: ${data.upt.pattern}`;
        } else {
            uptBox.style.display = 'none';
        }

        loading.style.display = 'none';
        content.style.display = 'flex';
    } catch (e) {
        loading.innerHTML = '<span style="color: var(--atlas-critical, #B43D3D);">Gagal mengambil data profil desa. Silakan coba kembali.</span>';
    }
}

function closeVillageModal() {
    const modal = document.getElementById('modal-village-detail');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeVillageModal();
});
</script>
