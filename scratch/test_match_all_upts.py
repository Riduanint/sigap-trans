import urllib.request
import json
import re
import psycopg2

# Database connection
conn = psycopg2.connect(
    dbname="sigap_trans",
    user="postgres",
    password="",
    host="127.0.0.1",
    port="5432"
)
cur = conn.cursor()

# Get all regencies except Barito Kuala (id = 6)
cur.execute("""
    SELECT r.id, r.name, u.id, u.upt_number, u.upt_name, u.current_village_name, u.latitude, u.longitude
    FROM upt_locations u
    JOIN regencies r ON r.id = u.regency_id
    WHERE r.id != 6
    ORDER BY r.id, u.id
""")
rows = cur.fetchall()

regency_code_map = {
    'Tanah Laut': '63.01',
    'Kota Baru': '63.02',
    'Banjar': '63.03',
    'Tapin': '63.05',
    'Hulu Sungai Selatan': '63.06',
    'Hulu Sungai Tengah': '63.07',
    'Hulu Sungai Utara': '63.08',
    'Tabalong': '63.09',
    'Tanah Bumbu': '63.10',
    'Balangan': '63.11',
}

# Group UPTs by regency
upts_by_regency = {}
for r_id, r_name, u_id, u_num, u_name, v_name, lat, lng in rows:
    if r_name not in upts_by_regency:
        upts_by_regency[r_name] = []
    upts_by_regency[r_name].append({
        'regency_id': r_id,
        'upt_id': u_id,
        'upt_number': u_num,
        'upt_name': u_name,
        'village_name': v_name or '',
        'lat': float(lat) if lat else None,
        'lng': float(lng) if lng else None
    })

print(f"Total UPTs to process (excluding Batola): {sum(len(v) for v in upts_by_regency.values())}")

# Cache downloaded village boundaries
villages_by_regency = {}
pattern = r"\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)"

def clean_name(s):
    if not s:
        return ""
    s = s.lower()
    s = re.sub(r'^(desa|ds\s*\.?|kelurahan|kel\s*\.?)\s+', '', s)
    s = re.sub(r'\s+definitif.*$', '', s)
    s = re.sub(r'\[.*\]', '', s)
    s = re.sub(r'[^a-z0-9\s]', ' ', s)
    s = re.sub(r'\s+', ' ', s).strip()
    return s

for reg_name, upt_list in upts_by_regency.items():
    code = regency_code_map.get(reg_name)
    if not code:
        print(f"No code for regency {reg_name}")
        continue
    
    url = f"https://raw.githubusercontent.com/cahyadsn/wilayah_boundaries/master/db/kel/63/wilayah_boundaries_kel_{code}.sql"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req, timeout=15) as resp:
        content = resp.read().decode('utf-8')
    
    matches = re.findall(pattern, content)
    villages = []
    for m in matches:
        kode, nama, lat, lng, path_str = m[0], m[1], float(m[2]), float(m[3]), m[4]
        try:
            raw_coords = json.loads(path_str)
            geojson_coords = []
            for poly in raw_coords:
                ring = [[round(pt[1], 6), round(pt[0], 6)] for pt in poly]
                geojson_coords.append(ring)
            geom = {
                'type': 'Polygon' if len(geojson_coords) == 1 else 'MultiPolygon',
                'coordinates': geojson_coords if len(geojson_coords) == 1 else [[r] for r in geojson_coords]
            }
            villages.append({
                'kode': kode,
                'nama': nama,
                'clean_nama': clean_name(nama),
                'centroid': [round(lng, 6), round(lat, 6)],
                'geometry': geom
            })
        except Exception as e:
            pass
            
    villages_by_regency[reg_name] = villages
    print(f"Regency {reg_name} ({code}): Loaded {len(villages)} Kemendagri villages")

# Now test matching
print("\n" + "="*80)
print("TESTING MATCHING FOR ALL 104 UPTs")
print("="*80)

matched = 0
unmatched = []

for reg_name, upt_list in upts_by_regency.items():
    v_list = villages_by_regency.get(reg_name, [])
    print(f"\n--- {reg_name} ({len(upt_list)} UPTs) ---")
    
    for upt in upt_list:
        raw_v = upt['village_name']
        raw_u = upt['upt_name']
        c_v = clean_name(raw_v)
        c_u = clean_name(raw_u)
        
        # 1. Exact match on clean village name
        best_match = None
        for v in v_list:
            if c_v and v['clean_nama'] == c_v:
                best_match = v
                break
                
        # 2. Substring or comma-separated match
        if not best_match and c_v:
            # Maybe village name is multiple: "Sumber Jaya, Kebun Raya"
            parts = [clean_name(p) for p in re.split(r'[,/]', raw_v)]
            for p in parts:
                if not p: continue
                for v in v_list:
                    if v['clean_nama'] == p or p in v['clean_nama'] or v['clean_nama'] in p:
                        best_match = v
                        break
                if best_match:
                    break

        # 3. Match against UPT name if village name didn't match
        if not best_match:
            parts_u = [clean_name(p) for p in re.split(r'[,/]', raw_u)]
            for p in parts_u:
                if not p or len(p) < 4: continue
                for v in v_list:
                    if v['clean_nama'] == p or p in v['clean_nama'] or v['clean_nama'] in p:
                        best_match = v
                        break
                if best_match:
                    break

        # 4. Nearest centroid distance match within regency if coordinates exist
        # (optional fallback)
        
        if best_match:
            matched += 1
            print(f"  [OK] UPT #{upt['upt_number']:03d} (ID: {upt['upt_id']:3d}) '{raw_u}' | '{raw_v}' -> '{best_match['nama']}' ({best_match['kode']})")
        else:
            unmatched.append(upt)
            print(f"  [NO] UPT #{upt['upt_number']:03d} (ID: {upt['upt_id']:3d}) '{raw_u}' | '{raw_v}' -> NO MATCH")

print(f"\nSUMMARY: Matched {matched} / {sum(len(v) for v in upts_by_regency.values())}")
if unmatched:
    print(f"Unmatched {len(unmatched)} UPTs:")
    for u in unmatched:
        print(f"  ID {u['upt_id']} ({u['upt_name']}) - Desa: '{u['village_name']}'")

