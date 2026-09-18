import urllib.request, json, time, os

regencies = [
    {'id': 1, 'name': 'Tapin', 'code_roman': 'I', 'osm_id': 'R12989889', 'color': '#8b5cf6'},        # Purple (like in user screenshot)
    {'id': 2, 'name': 'Hulu Sungai Utara', 'code_roman': 'II', 'osm_id': 'R12989887', 'color': '#06b6d4'}, # Cyan
    {'id': 3, 'name': 'Balangan', 'code_roman': 'III', 'osm_id': 'R12989888', 'color': '#3b82f6'},     # Blue
    {'id': 4, 'name': 'Tabalong', 'code_roman': 'IV', 'osm_id': 'R12989891', 'color': '#10b981'},     # Emerald
    {'id': 5, 'name': 'Tanah Laut', 'code_roman': 'V', 'osm_id': 'R12989884', 'color': '#f59e0b'},    # Amber
    {'id': 6, 'name': 'Barito Kuala', 'code_roman': 'VI', 'osm_id': 'R12989892', 'color': '#a855f7'},  # Purple / Violet (Marabahan)
    {'id': 7, 'name': 'Kota Baru', 'code_roman': 'VII', 'osm_id': 'R12989882', 'color': '#ec4899'},   # Pink / Rose
    {'id': 8, 'name': 'Tanah Bumbu', 'code_roman': 'VIII', 'osm_id': 'R12989883', 'color': '#f97316'}, # Orange
    {'id': 9, 'name': 'Banjar', 'code_roman': 'IX', 'osm_id': 'R12989890', 'color': '#14b8a6'}        # Teal
]

def simplify_coords(coords, step=2):
    # If coords has many vertices, optionally keep them or slight decimation if huge
    return coords

features = []

for reg in regencies:
    osm_id = reg['osm_id']
    url = f"https://nominatim.openstreetmap.org/lookup?osm_ids={osm_id}&format=geojson&polygon_geojson=1"
    print(f"Fetching {reg['name']} ({osm_id})...")
    req = urllib.request.Request(url, headers={'User-Agent': 'SIGAP-TRANS-WebGIS/1.0 (disnakertrans@kalselprov.go.id)'})
    try:
        with urllib.request.urlopen(req, timeout=25) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            fc = data.get('features', [])
            if fc:
                geom = fc[0].get('geometry', {})
                feature = {
                    'type': 'Feature',
                    'id': reg['id'],
                    'properties': {
                        'id': reg['id'],
                        'name': reg['name'],
                        'code_roman': reg['code_roman'],
                        'color': reg['color'],
                    },
                    'geometry': geom
                }
                features.append(feature)
                print(f"  -> SUCCESS ({geom.get('type')})")
            else:
                print(f"  -> No feature found in response!")
    except Exception as e:
        print(f"  -> Error: {e}")
    time.sleep(1) # Be respectful to Nominatim rate limits

os.makedirs('public/data', exist_ok=True)
output_path = 'public/data/kalsel_regencies.geojson'
geojson_collection = {
    'type': 'FeatureCollection',
    'features': features
}

with open(output_path, 'w', encoding='utf-8') as f:
    json.dump(geojson_collection, f)

print(f"\nDone! Total features saved: {len(features)} into {output_path}")
