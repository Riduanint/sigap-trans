import xlrd

wb = xlrd.open_workbook('docs/Data UPT Kalsel.xls')
sheet = wb.sheet_by_name('UPT serah sejak 53')

# Inspect HSU, Tala, Kotabaru rows
for r in range(sheet.nrows):
    c0 = str(sheet.cell_value(r, 0)).strip()
    if c0.endswith('.') and c0[:-1].isdigit():
        num = int(c0[:-1])
        # Kotabaru UPTs: 72 to 110, HSU: 10 to 15, Tala: 29 to 51
        if num in [13, 14, 29, 30, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 111, 112, 113]:
            vals = [(c, sheet.cell_value(r, c)) for c in range(sheet.ncols) if str(sheet.cell_value(r, c)).strip()]
            print(f'UPT {num:3d} (r {r:3d}): {vals}')
