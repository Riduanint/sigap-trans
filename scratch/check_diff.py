from aggregate_upts import upts

kab_subtotals = {
    1: ('TAPIN', 2800, 12023, 3549, 12437),
    2: ('HULU SUNGAI UTARA', 2257, 8876, 2131, 9755),
    3: ('BALANGAN', 675, 2414, 705, 2743),
    4: ('TABALONG', 4019, 16137, 4031, 16159),
    5: ('TANAH LAUT', 11751, 47062, 11797, 48400),
    6: ('BARITO KUALA', 9809, 43003, 10323, 44873),
    7: ('KOTA BARU', 28111, 110083, 28200, 111533),
    8: ('TANAH BUMBU', 775, 3018, 702, 3094),
    9: ('BANJAR', 3504, 13691, 3504, 13877)
}

by_kab = {}
for u in upts:
    rid = u['regency_id']
    if rid not in by_kab:
        by_kab[rid] = {'kk_in': 0, 'jiwa_in': 0, 'kk_out': 0, 'jiwa_out': 0, 'count': 0}
    by_kab[rid]['kk_in'] += u['kk_in']
    by_kab[rid]['jiwa_in'] += u['jiwa_in']
    by_kab[rid]['kk_out'] += u['kk_serah']
    by_kab[rid]['jiwa_out'] += u['jiwa_serah']
    by_kab[rid]['count'] += 1

for rid, exp in kab_subtotals.items():
    act = by_kab.get(rid, {})
    k_diff = act['kk_out'] - exp[3]
    name = exp[0]
    count = act['count']
    kout = act['kk_out']
    kexp = exp[3]
    print(f'{name:18s} ({count:2d} UPT): KK Out = {kout:6d} | Exp = {kexp:6d} | Diff = {k_diff:4d}')
