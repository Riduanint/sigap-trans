import urllib.request
import json
import re

url = 'https://raw.githubusercontent.com/cahyadsn/wilayah_boundaries/master/db/kel/63/wilayah_boundaries_kel_63.04.sql'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
print("Downloading Barito Kuala village boundaries from repository...")
with urllib.request.urlopen(req, timeout=20) as resp:
    content = resp.read().decode('utf-8')

# Parse all records
# Format: ('kode','nama',lat,lng,'path')
pattern = r"\('(\d{2}\.\d{2}\.\d{2}\.\d{4})',\s*'([^']+)',\s*([-\d.]+),\s*([-\d.]+),\s*'([^']+)'\)"
matches = re.findall(pattern, content)
print(f"Total Batola village boundaries parsed: {len(matches)}")

villages_by_name = {}
villages_by_code = {}

for m in matches:
    kode, nama, lat, lng, path_str = m[0], m[1], float(m[2]), float(m[3]), m[4]
    try:
        raw_coords = json.loads(path_str)
        # Coordinates in raw_coords are [[[lat, lng], ...]]
        # We need GeoJSON standard [[[lng, lat], ...]]
        geojson_coords = []
        for poly in raw_coords:
            ring = []
            for pt in poly:
                ring.append([round(pt[1], 6), round(pt[0], 6)])
            geojson_coords.append(ring)
            
        geom = {
            'type': 'Polygon' if len(geojson_coords) == 1 else 'MultiPolygon',
            'coordinates': geojson_coords if len(geojson_coords) == 1 else [[r] for r in geojson_coords]
        }
        
        entry = {
            'kode': kode,
            'nama': nama,
            'centroid': [round(lng, 6), round(lat, 6)],
            'geometry': geom
        }
        villages_by_name[nama.strip().lower()] = entry
        villages_by_code[kode] = entry
    except Exception as e:
        print(f"Error parsing geometry for {nama}: {e}")

# Mapping of the 20 Batola UPTs (IDs 52 to 71) to exact village codes or names
upt_mapping = {
    52: {'name': 'Tamban', 'village': 'Tamban Bangun', 'code': '63.04.02.2003'},
    53: {'name': 'Marabahan', 'village': 'Marabahan Baru', 'code': '63.04.04.2014'},
    54: {'name': 'Balandean', 'village': 'Belandean', 'code': '63.04.06.2019'},
    55: {'name': 'Barambai', 'village': 'Barambai Kolam Kanan', 'code': '63.04.14.2010'},
    56: {'name': 'Belawang', 'village': 'Kolam Makmur', 'code': '63.04.16.2010'},
    57: {'name': 'Tabunganen', 'village': 'Tanggul Rejo', 'code': '63.04.01.2014'},
    58: {'name': 'Sei Muhur', 'village': 'Danau Karya', 'code': '63.04.03.2015'},
    59: {'name': 'Sakalagun', 'village': 'Dwi Puspitasari', 'code': '63.04.16.2003'}, # Simpang Jaya/Wanaraya area
    60: {'name': 'Sei Seluang', 'village': 'Simpang Jaya', 'code': '63.04.16.2003'},
    61: {'name': 'Sei Puntik I / Terantang', 'village': 'Karang Indah', 'code': '63.04.06.2021'},
    62: {'name': 'Sei Puntik II / Danda Besar', 'village': 'Danda Jaya', 'code': '63.04.07.2009'},
    63: {'name': 'Marabahan SP.1', 'village': 'Sido Makmur', 'code': '63.04.14.2004'},
    64: {'name': 'Marabahan SP.2', 'village': 'Sido Makmur', 'code': '63.04.14.2004'},
    65: {'name': 'Bahandang Sp.1', 'village': 'Bahandang', 'code': '63.04.17.2003'},
    66: {'name': 'Bahandang Sp.2', 'village': 'Tatah Alayung', 'code': '63.04.06.2009'},
    67: {'name': 'Tumih Roham Raya', 'village': 'Gandaraya', 'code': '63.04.03.2005'},
    68: {'name': 'Cahaya Baru/Sempurna', 'village': 'Cahaya Baru', 'code': '63.04.17.2005'},
    69: {'name': 'Simpang Arja/Sinar Baru', 'village': 'Sinar Baru', 'code': '63.04.07.2007'},
    70: {'name': 'Simpang Nungki', 'village': 'Simpang Nungki', 'code': '63.04.09.2003'},
    71: {'name': 'Jejangkit Timur', 'village': 'Jejangkit Timur', 'code': '63.04.17.2004'},
}

print("\n--- Validating Village Mappings for all 20 Batola UPTs ---")
final_upt_data = {}
for upt_id, info in upt_mapping.items():
    code = info['code']
    data = villages_by_code.get(code)
    if not data:
        # Fallback by name
        data = villages_by_name.get(info['village'].lower())
        
    if data:
        print(f"UPT {upt_id:02d} ({info['name']}) -> {data['nama']} ({data['kode']}) [Centroid: {data['centroid']}, Geotype: {data['geometry']['type']}]")
        final_upt_data[upt_id] = {
            'upt_id': upt_id,
            'upt_name': info['name'],
            'village_name': data['nama'],
            'kode_kemendagri': data['kode'],
            'centroid': data['centroid'],
            'geometry': data['geometry']
        }
    else:
        print(f"FAILED to find data for UPT {upt_id}: {info}")

# Save JSON file for easy PHP sync
with open('scratch/batola_upt_village_boundaries.json', 'w', encoding='utf-8') as f:
    json.dump(final_upt_data, f, indent=2)

print(f"\nSaved {len(final_upt_data)} UPT village boundaries to scratch/batola_upt_village_boundaries.json")
