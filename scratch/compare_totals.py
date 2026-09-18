import xlrd

wb = xlrd.open_workbook('docs/Data UPT Kalsel.xls')
sheet = wb.sheet_by_name('UPT serah sejak 53')

kab_subtotals = {
    'TAPIN': (2800, 12023, 3549, 12437),
    'HULU SUNGAI UTARA': (2257, 8876, 2131, 9755),
    'BALANGAN': (675, 2414, 705, 2743),
    'TABALONG': (4019, 16137, 4031, 16159),
    'TANAH LAUT': (11751, 47062, 11797, 48400),
    'BARITO KUALA': (9809, 43003, 10323, 44873),
    'KOTA BARU': (28111, 110083, 28200, 111533),
    'TANAH BUMBU': (775, 3018, 702, 3094),
    'BANJAR': (3504, 13691, 3504, 13877)
}

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

cur_kab = 'START'
kab_data = {}

for r in range(sheet.nrows):
    c0 = str(sheet.cell_value(r, 0)).strip()
    c1 = str(sheet.cell_value(r, 1)).strip()
    for k in regency_map:
        if k in (c0 + ' ' + c1).upper() and (':' in c1 or 'I' in c0 or 'V' in c0 or 'X' in c0 or 'KABUPATEN' in (c0 + c1).upper()):
            cur_kab = k
            kab_data[cur_kab] = {'kk_in': 0, 'jiwa_in': 0, 'kk_out': 0, 'jiwa_out': 0, 'rows': []}
            break
            
    is_upt = (c0.endswith('.') and c0[:-1].isdigit()) or ('LAJAR PAPUYUAN' in c1.upper())
    if is_upt and cur_kab in kab_data:
        def to_i(c):
            try:
                return int(float(str(sheet.cell_value(r, c)).strip()))
            except:
                return 0
        kk_in = to_i(5)
        jiwa_in = to_i(6)
        kk_out = to_i(9)
        jiwa_out = to_i(10)
        
        kab_data[cur_kab]['kk_in'] += kk_in
        kab_data[cur_kab]['jiwa_in'] += jiwa_in
        kab_data[cur_kab]['kk_out'] += kk_out
        kab_data[cur_kab]['jiwa_out'] += jiwa_out
        kab_data[cur_kab]['rows'].append((r, c0, c1, kk_in, jiwa_in, kk_out, jiwa_out))

for k, exp in kab_subtotals.items():
    act = kab_data.get(k, {})
    kin = act.get('kk_in', 0)
    jin = act.get('jiwa_in', 0)
    kout = act.get('kk_out', 0)
    jout = act.get('jiwa_out', 0)
    print(f'-- {k} (Rows: {len(act.get("rows", []))}) --')
    print(f'  KK In:   Actual = {kin:6d}, Expected = {exp[0]:6d} (Diff: {kin - exp[0]:6d})')
    print(f'  Jiwa In: Actual = {jin:6d}, Expected = {exp[1]:6d} (Diff: {jin - exp[1]:6d})')
    print(f'  KK Out:  Actual = {kout:6d}, Expected = {exp[2]:6d} (Diff: {kout - exp[2]:6d})')
    print(f'  Jiwa Out:Actual = {jout:6d}, Expected = {exp[3]:6d} (Diff: {jout - exp[3]:6d})')
