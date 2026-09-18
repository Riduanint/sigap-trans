import xlrd

wb = xlrd.open_workbook('docs/Data UPT Kalsel.xls')
sheet = wb.sheet_by_name('UPT serah sejak 53')

for r in range(sheet.nrows):
    c0 = str(sheet.cell_value(r, 0)).strip()
    c1 = str(sheet.cell_value(r, 1)).strip()
    is_main = c0.endswith('.') and c0[:-1].isdigit()
    is_header = any(k in (c0 + ' ' + c1).upper() for k in ['JUMLAH', 'KABUPATEN', 'NO', 'LOKASI', 'TAPIN', 'HULU', 'BALANGAN', 'TABALONG', 'TANAH', 'BARITO', 'KOTA', 'BANJAR']) or c0 in ['1.0', '1.'] and c1 in ['2.0']
    
    vals = [(c, sheet.cell_value(r, c)) for c in range(sheet.ncols) if str(sheet.cell_value(r, c)).strip()]
    if not is_main and not is_header and vals:
        print(f'Row {r:3d}: c0=\"{c0}\", c1=\"{c1}\" -> {vals}')
