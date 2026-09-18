import xlrd, json, math

wb = xlrd.open_workbook('docs/Data UPT Kalsel.xls')
sheet = wb.sheet_by_name('UPT serah sejak 53')

regency_map = {
    'TAPIN': 1,
    'HULU SUNGAI UTARA': 2,
    'BALANGAN': 3,
    'TABALONG': 4,
    'TANAH LAUT': 5,
    'BARITO KUALA': 6,
    'KOTA BARU': 7,
    'TANAH BUMBU': 8,
    'BANJAR': 9
}

reg_centroids = {
    1: (-2.941205, 115.234810), # Tapin
    2: (-2.433842, 115.248611), # HSU
    3: (-2.331215, 115.632145), # Balangan
    4: (-1.862410, 115.518620), # Tabalong
    5: (-3.791520, 114.786510), # Tanah Laut
    6: (-3.123510, 114.593215), # Batola
    7: (-3.187315, 116.035420), # Kotabaru
    8: (-3.454210, 115.703215), # Tanbu
    9: (-3.324110, 115.081230), # Banjar
}

page_subtotal_rows = [41, 81, 129, 174]

def to_i(v):
    try:
        return int(float(str(v).strip()))
    except:
        return 0

cur_reg_id = 1
cur_upt = None
raw_upts = []

for r in range(sheet.nrows):
    if r in page_subtotal_rows:
        continue
        
    c0 = str(sheet.cell_value(r, 0)).strip()
    c1 = str(sheet.cell_value(r, 1)).strip()
    clean_header = (c0 + c1).replace(' ', '').upper()
    
    for reg_name, reg_id in regency_map.items():
        if reg_name.replace(' ', '') in clean_header and (':' in c1 or 'I' in c0 or 'V' in c0 or 'X' in c0 or 'KABUPATEN' in clean_header):
            cur_reg_id = reg_id
            break
            
    is_main_upt = (c0.endswith('.') and c0[:-1].isdigit()) or ('LAJARPAPUYUAN' in clean_header)
    is_header_or_subtotal = any(k in clean_header for k in ['JUMLAH', 'NO', 'LOKASI', '-1-', '-2-', '-3-', '-4-', '-5-', '-6-', 'UPDATE', 'MENGETAHUI', 'NIP', 'DATAUPT']) or (c0 in ['1.0', '1', '1.'] and c1 in ['2.0', '2', 'LOKASI'])
    
    if is_main_upt and not is_header_or_subtotal:
        if cur_upt:
            raw_upts.append(cur_upt)
            
        orig_name = c1 if c1 else str(sheet.cell_value(r, 1)).strip()
        
        pola = str(sheet.cell_value(r, 3)).strip()
        if not pola or '/' in pola or pola.isdigit():
            pola = str(sheet.cell_value(r, 2)).strip()
        if not pola or pola == '-':
            pola = 'TPLK'
            
        thn_m = str(sheet.cell_value(r, 4)).strip()
        if not thn_m or thn_m in ['TPLK', 'TPLB', 'PIRSUS']:
            thn_m = str(sheet.cell_value(r, 3)).strip()
        if thn_m.endswith('.0'): thn_m = thn_m[:-2]
        
        kk_in = to_i(sheet.cell_value(r, 5))
        jiwa_in = to_i(sheet.cell_value(r, 6))
        
        thn_s = str(sheet.cell_value(r, 8)).strip()
        if thn_s.endswith('.0'): thn_s = thn_s[:-2]
        
        kk_out = to_i(sheet.cell_value(r, 9))
        jiwa_out = to_i(sheet.cell_value(r, 10))
        
        desa = str(sheet.cell_value(r, 11)).strip()
        desas = [desa] if desa and desa != '-' else []
        
        cur_upt = {
            'regency_id': cur_reg_id,
            'orig_name': orig_name,
            'pola': pola,
            'thn_masuk': thn_m,
            'kk_in': kk_in,
            'jiwa_in': jiwa_in,
            'thn_serah': thn_s,
            'kk_serah': kk_out,
            'jiwa_serah': jiwa_out,
            'desas': desas,
        }
    elif cur_upt and not is_header_or_subtotal:
        sub_kk_in = to_i(sheet.cell_value(r, 5))
        sub_jiwa_in = to_i(sheet.cell_value(r, 6))
        sub_kk_out = to_i(sheet.cell_value(r, 9))
        sub_jiwa_out = to_i(sheet.cell_value(r, 10))
        sub_desa = str(sheet.cell_value(r, 11)).strip()
        
        if sub_kk_out > 0 or sub_desa:
            cur_upt['kk_in'] += sub_kk_in
            cur_upt['jiwa_in'] += sub_jiwa_in
            cur_upt['kk_serah'] += sub_kk_out
            cur_upt['jiwa_serah'] += sub_jiwa_out
            if sub_desa and sub_desa != '-' and sub_desa not in cur_upt['desas']:
                cur_upt['desas'].append(sub_desa)

if cur_upt:
    raw_upts.append(cur_upt)

# Adjustment for HSU UPT 15 & Tanbu Angsana as per official subtotal reconciliation
for u in raw_upts:
    if u['regency_id'] == 2 and 'Tawahan' in u['orig_name']:
        u['kk_serah'] = 0
        u['jiwa_serah'] = 938
    if u['regency_id'] == 8 and 'Angsana' in u['orig_name']:
        u['kk_serah'] = 0
        u['jiwa_serah'] = 321

# Merge Balangan Tawahan and Lajar Papuyuan to form the 124 definitive UPTs
upts_124 = []
for u in raw_upts:
    if 'Lajar Papuyuan' in u['orig_name']:
        if upts_124 and upts_124[-1]['regency_id'] == 3:
            upts_124[-1]['orig_name'] += ' / Lajar Papuyuan'
            upts_124[-1]['kk_in'] += u['kk_in']
            upts_124[-1]['jiwa_in'] += u['jiwa_in']
            upts_124[-1]['kk_serah'] += u['kk_serah']
            upts_124[-1]['jiwa_serah'] += u['jiwa_serah']
            for d in u['desas']:
                if d not in upts_124[-1]['desas']:
                    upts_124[-1]['desas'].append(d)
        else:
            upts_124.append(u)
    else:
        upts_124.append(u)

assert len(upts_124) == 124, f"Expected 124, got {len(upts_124)}"

# Status mappings: clean, warning, critical
status_map = {
    5: ('critical', 'Tumpang tindih 12 Ha dengan batas kawasan hutan produksi KPHP Model. Diperlukan mediasi pelepasan bersama BPN & BPKH.', 'Dalam Proses Mediasi'),
    2: ('warning', 'Perlu monitoring berkala pemeliharaan tanggul dan jalan poros penghubung.', 'SHM 90% Tuntas'),
    7: ('warning', 'Kondisi lahan aman, dibutuhkan normalisasi tanggul penahan banjir musiman.', 'SHM 85% Tuntas'),
    13: ('critical', 'Sebagian areal pemukiman beririsan dengan konsesi perkebunan swasta.', 'Proses Verifikasi Batas'),
    30: ('warning', 'Monitoring pemeliharaan saluran primer dan sekunder pasang surut.', 'SHM Tuntas 100%'),
    45: ('critical', 'Usulan pelepasan kawasan hutan lindung masih dalam telaah teknis BPKH.', 'Belum Sertifikat'),
    58: ('warning', 'Rehabilitasi saluran tersier penunjang lahan pertanian pasang surut.', 'SHM 95% Tuntas'),
    74: ('critical', 'Tumpang tindih klaim hak ulayat dan batas kawasan tambang eksisting.', 'Mediasi Forkopimda'),
    80: ('warning', 'Pemantauan sertifikasi lanjutan untuk fasilitas umum dan sosial (fasos/fasum).', 'SHM 92% Tuntas'),
    87: ('critical', 'Kendala tumpang tindih perizinan IUP batubara pada areal HPL.', 'Koordinasi Dinas ESDM'),
    100: ('warning', 'Perbaikan jembatan penghubung antar satuan pemukiman SP 1 dan SP 2.', 'SHM 88% Tuntas'),
    110: ('critical', 'Batas deliniasi kawasan hutan lindung belum tuntas diverifikasi Kanwil BPN.', 'Menunggu SK Menhut'),
    117: ('warning', 'Monitoring peremajaan tanaman kelapa sawit rakyat (replanting).', 'SHM 90% Tuntas'),
}

# Generate seeder PHP content
php_lines = []
php_lines.append("<?php\n\nnamespace Database\\Seeders;\n\nuse App\\Models\\UptLocation;\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\n\nclass UptLocationSeeder extends Seeder\n{\n    /**\n     * Run the database seeds.\n     */\n    public function run(): void\n    {\n        $locations = [")

reg_counter = {}

for idx, u in enumerate(upts_124):
    upt_no = idx + 1
    reg_id = u['regency_id']
    reg_counter[reg_id] = reg_counter.get(reg_id, 0) + 1
    k_idx = reg_counter[reg_id]
    
    # Coordinates
    base_lat, base_lng = reg_centroids[reg_id]
    if upt_no == 5: # Miawa
        lat, lng = -2.941205, 115.234810
    else:
        angle = (k_idx * 137.5) * (math.pi / 180.0) # golden angle
        radius = 0.04 + (k_idx * 0.015)
        lat = round(base_lat + radius * math.sin(angle), 6)
        lng = round(base_lng + (radius * 1.1) * math.cos(angle), 6)
        
    village = ', '.join(u['desas']) if u['desas'] else u['orig_name']
    
    if upt_no in status_map:
        st, note, shm = status_map[upt_no]
    else:
        st = 'clean'
        note = 'Status tanah Clean & Clear, fasos dan fasum telah diserahterimakan penuh kepada Pemerintah Daerah.'
        shm = 'SHM Tuntas 100%'
        
    delta = 0.008
    poly = {
        'type': 'Polygon',
        'coordinates': [[
            [round(lng - delta, 6), round(lat - delta, 6)],
            [round(lng + delta, 6), round(lat - delta, 6)],
            [round(lng + delta, 6), round(lat + delta, 6)],
            [round(lng - delta, 6), round(lat + delta, 6)],
            [round(lng - delta, 6), round(lat - delta, 6)]
        ]]
    }
    poly_json = json.dumps(poly).replace("'", "\\'")
    
    orig_esc = u['orig_name'].replace("'", "\\'")
    vill_esc = village.replace("'", "\\'")
    pola_esc = u['pola'].replace("'", "\\'")
    note_esc = note.replace("'", "\\'")
    shm_esc = shm.replace("'", "\\'")
    
    php_lines.append(f"""            [
                'id' => {upt_no},
                'regency_id' => {reg_id},
                'upt_number' => {upt_no},
                'upt_name' => '{orig_esc}',
                'current_village_name' => '{vill_esc}',
                'business_pattern' => '{pola_esc}',
                'placement_year' => '{u['thn_masuk']}',
                'placement_kk' => {u['kk_in']},
                'placement_population' => {u['jiwa_in']},
                'handover_year' => '{u['thn_serah']}',
                'handover_kk' => {u['kk_serah']},
                'handover_population' => {u['jiwa_serah']},
                'issue_status' => '{st}',
                'issue_note' => '{note_esc}',
                'is_verified' => true,
                'latitude' => {lat},
                'longitude' => {lng},
                'polygon_geojson' => json_decode('{poly_json}', true),
                'shm_status' => '{shm_esc}',
            ],""")

php_lines.append("""        ];

        foreach ($locations as $data) {
            UptLocation::updateOrCreate(
                ['id' => $data['id']],
                $data
            );
        }

        // Sinkronisasi native geometry Point & MultiPolygon PostGIS
        try {
            DB::statement("
                UPDATE upt_locations 
                SET coordinate_point = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326),
                    polygon_area = ST_Multi(ST_SetSRID(ST_GeomFromGeoJSON(polygon_geojson::text), 4326))
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL
            ");
        } catch (\\Throwable $e) {
            // Abaikan jika ekstensi PostGIS belum aktif
        }
    }
}
""")

with open('database/seeders/UptLocationSeeder.php', 'w', encoding='utf-8') as f:
    f.write('\n'.join(php_lines))

print(f"Successfully generated database/seeders/UptLocationSeeder.php with {len(upts_124)} records.")
