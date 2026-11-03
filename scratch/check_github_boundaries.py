import urllib.request
import re

codes = {
    '63.01': 'Tanah Laut',
    '63.02': 'Kota Baru',
    '63.03': 'Banjar',
    '63.04': 'Barito Kuala',
    '63.05': 'Tapin',
    '63.06': 'Hulu Sungai Selatan',
    '63.07': 'Hulu Sungai Tengah',
    '63.08': 'Hulu Sungai Utara',
    '63.09': 'Tabalong',
    '63.10': 'Tanah Bumbu',
    '63.11': 'Balangan',
}

for code, name in codes.items():
    url = f"https://raw.githubusercontent.com/cahyadsn/wilayah_boundaries/master/db/kel/63/wilayah_boundaries_kel_{code}.sql"
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=10) as resp:
            data = resp.read(2048).decode('utf-8')
            print(f"[{code}] {name:20s}: FOUND (Length > 2KB)")
    except Exception as e:
        print(f"[{code}] {name:20s}: ERROR - {e}")

