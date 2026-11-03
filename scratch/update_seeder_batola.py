import json
import re

with open('scratch/batola_upt_village_boundaries.json', 'r', encoding='utf-8') as f:
    villages = json.load(f)

with open('database/seeders/UptLocationSeeder.php', 'r', encoding='utf-8') as f:
    seeder_code = f.read()

for upt_id_str, data in villages.items():
    upt_id = int(upt_id_str)
    lng = data['centroid'][0]
    lat = data['centroid'][1]
    geom_json = json.dumps(data['geometry']).replace("'", "\\'")
    
    # Match block for this upt_id:
    # 'id' => {upt_id}, ... 'latitude' => ..., 'longitude' => ..., 'polygon_geojson' => json_decode('...', true),
    block_pattern = rf"(\[\s*'id'\s*=>\s*{upt_id},.*?)(?:'latitude'\s*=>\s*[-\d.]+,)\s*(?:'longitude'\s*=>\s*[-\d.]+,)\s*(?:'polygon_geojson'\s*=>\s*json_decode\('[^']+',\s*true\),)"
    
    replacement = rf"\1'latitude' => {lat},\n                'longitude' => {lng},\n                'polygon_geojson' => json_decode('{geom_json}', true),"
    
    new_seeder_code, count = re.subn(block_pattern, replacement, seeder_code, flags=re.DOTALL)
    if count > 0:
        seeder_code = new_seeder_code
        print(f"Updated seeder for UPT {upt_id} ({data['upt_name']})")
    else:
        print(f"WARNING: Regex did not match for UPT {upt_id}")

with open('database/seeders/UptLocationSeeder.php', 'w', encoding='utf-8') as f:
    f.write(seeder_code)

print("Seeder file updated successfully!")
