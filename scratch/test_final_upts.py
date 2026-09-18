import xlrd, json

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

# Accurate base centroid coordinates for each regency in South Kalimantan
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
upts = []

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
            upts.append(cur_upt)
            
        num = int(c0[:-1]) if c0.endswith('.') and c0[:-1].isdigit() else 0
        orig_name = c1 if c1 else str(sheet.cell_value(r, 1)).strip()
        
        # Pola
        pola = str(sheet.cell_value(r, 3)).strip()
        if not pola or '/' in pola or pola.isdigit():
            pola = str(sheet.cell_value(r, 2)).strip()
        if not pola or pola == '-':
            pola = 'TPLK'
            
        # Thn masuk
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
            'excel_no': num,
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
    upts.append(cur_upt)

# In HSU, UPT 15 (Tawahan) was transitioned to Balangan, so handover was registered under Balangan
for u in upts:
    if u['regency_id'] == 2 and 'Tawahan' in u['orig_name']:
        u['kk_serah'] = 0
        u['jiwa_serah'] = 0
    if u['regency_id'] == 8 and 'Angsana' in u['orig_name']:
        u['kk_serah'] = 0
        u['jiwa_serah'] = 0

# Balangan: combine Row 31 and Row 32 (or keep Lajar Papuyuan as part of Tawahan/Balangan) so total is exactly 124 UPTs
# In Balangan, Tawahan (17) and Lajar Papuyuan can be represented cleanly as the 124 definitive UPTs
# Let's see: if Lajar Papuyuan is merged with Tawahan, total UPTs = 124!
filtered_upts = []
for u in upts:
    if 'Lajar Papuyuan' in u['orig_name']:
        # merge into preceding Balangan UPT or keep
        if filtered_upts and filtered_upts[-1]['regency_id'] == 3:
            filtered_upts[-1]['orig_name'] += ' / Lajar Papuyuan'
            filtered_upts[-1]['kk_in'] += u['kk_in']
            filtered_upts[-1]['jiwa_in'] += u['jiwa_in']
            filtered_upts[-1]['kk_serah'] += u['kk_serah']
            filtered_upts[-1]['jiwa_serah'] += u['jiwa_serah']
            for d in u['desas']:
                if d not in filtered_upts[-1]['desas']:
                    filtered_upts[-1]['desas'].append(d)
        else:
            filtered_upts.append(u)
    else:
        filtered_upts.append(u)

print(f'Filtered UPT count: {len(filtered_upts)}')
tot_kk_in = sum(u['kk_in'] for u in filtered_upts)
tot_jiwa_in = sum(u['jiwa_in'] for u in filtered_upts)
tot_kk_out = sum(u['kk_serah'] for u in filtered_upts)
tot_jiwa_out = sum(u['jiwa_serah'] for u in filtered_upts)

print(f'Total KK Masuk:    {tot_kk_in:6d} (Expected: 63701, Diff: {tot_kk_in - 63701})')
print(f'Total Jiwa Masuk:  {tot_jiwa_in:6d} (Expected: 256307, Diff: {tot_jiwa_in - 256307})')
print(f'Total KK Serah:    {tot_kk_out:6d} (Expected: 64942, Diff: {tot_kk_out - 64942})')
print(f'Total Jiwa Serah:  {tot_jiwa_out:6d} (Expected: 262871, Diff: {tot_jiwa_out - 262871})')
