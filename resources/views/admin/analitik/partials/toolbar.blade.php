<div class="atlas-panel mb-5" style="border-radius: 8px; border: 1px solid var(--atlas-border); background: #ffffff;">
    <div style="padding: 16px 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
        {{-- Quick Access Pills --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px;">
            @php
                $domains = [
                    'upt' => ['icon' => 'map', 'label' => 'Data UPT & Wilayah'],
                    'skp' => ['icon' => 'globe', 'label' => 'Kawasan & SKP'],
                    'kependudukan' => ['icon' => 'users', 'label' => 'Kependudukan'],
                    'pertanahan' => ['icon' => 'layers', 'label' => 'Pertanahan & SHM'],
                    'dokumen' => ['icon' => 'folder', 'label' => 'Arsip Dokumen'],
                ];
            @endphp
            @foreach($domains as $key => $d)
                @php $isActive = ($domain === $key); @endphp
                <a href="{{ route('admin.analitik.index', array_filter(['domain' => $key, 'regency_id' => $regencyId])) }}"
                   class="atlas-button {{ $isActive ? 'atlas-button--primary' : '' }}"
                   style="font-size: 13px; padding: 6px 14px; border-radius: 6px; font-weight: {{ $isActive ? '600' : '500' }}; transition: all 0.15s ease;">
                    <x-admin.icon :name="$d['icon']" />
                    <span>{{ $d['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Filter Kabupaten & Tombol Cetak / Ekspor --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-left: auto;">
            {{-- Filter Kabupaten --}}
            <form method="GET" action="{{ route('admin.analitik.index') }}" id="analytics-filter-form" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                <input type="hidden" name="domain" value="{{ $domain }}">
                <label for="filter-regency" class="atlas-muted" style="font-size: 13px; font-weight: 500; white-space: nowrap;">Kabupaten:</label>
                <select name="regency_id" id="filter-regency" onchange="document.getElementById('analytics-filter-form').submit()"
                        class="atlas-select" style="font-size: 13px; padding: 6px 12px; height: 36px; border-radius: 6px; min-width: 180px;">
                    <option value="all">Semua Kabupaten</option>
                    @foreach($allRegencies as $r)
                        <option value="{{ $r->id }}" {{ $regencyId == $r->id ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div style="height: 24px; width: 1px; background: var(--atlas-border); margin: 0 4px;" class="hidden sm:block"></div>

            {{-- Tombol Cetak Laporan PDF --}}
            @if($exportPdfUrl)
                <a href="{{ $exportPdfUrl }}" target="_blank" rel="noopener" class="atlas-button" style="font-size: 13px; height: 36px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <x-admin.icon name="download" />
                    <span>Cetak PDF</span>
                </a>
            @endif

            {{-- Tombol Unduh Excel/CSV (Jika Ada) --}}
            @if($exportExcelUrl)
                <a href="{{ $exportExcelUrl }}" class="atlas-button" style="font-size: 13px; height: 36px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <x-admin.icon name="download" />
                    <span>Unduh Excel</span>
                </a>
            @endif
        </div>
    </div>
</div>

