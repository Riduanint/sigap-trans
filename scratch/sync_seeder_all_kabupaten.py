import json
import re

with open('scratch/all_kabupaten_upt_village_boundaries.json', 'r', encoding='utf-8') as f:
    villages = json.load(f)

with open('database/seeders/UptLocationSeeder.php', 'r', encoding='utf-8') as f:
    seeder_code = f.read()

updated = 0
failed = 0

for upt_id_str, data in villages.items():
    upt_id = int(upt_id_str)
    # Never touch Batola (52 to 71)
    if 52 <= upt_id <= 71:
        continue

    lng = data['centroid'][0]
    lat = data['centroid'][1]
    geom_json = json.dumps(data['geometry']).replace("'", "\\'")

    # Pattern matches the latitude, longitude, polygon_geojson block for this specific upt_id
    # Note: in UptLocationSeeder, format is:
    # 'id' => {upt_id},
    # ...
    # 'latitude' => ...,
    # 'longitude' => ...,
    # 'polygon_geojson' => json_decode('...', true),
    block_pattern = rf"(\[\s*'id'\s*=>\s*{upt_id},.*?)(?:'latitude'\s*=>\s*[-\d.]+,\s*)(?:'longitude'\s*=>\s*[-\d.]+,\s*)(?:'polygon_geojson'\s*=>\s*json_decode\('[^']+',\s*true\),)"

    replacement = rf"\1'latitude' => {lat},\n                'longitude' => {lng},\n                'polygon_geojson' => json_decode('{geom_json}', true),"

    new_seeder_code, count = re.subn(block_pattern, replacement, seeder_code, flags=re.DOTALL)
    if count > 0:
        seeder_code = new_seeder_code
        updated += 1
    else:
        print(f"FAILED to match regex for UPT ID {upt_id}")
        failed += 1

print(f"\nRegex matching result: Updated {updated} UPTs in seeder, Failed {failed}")

if failed == 0:
    with open('database/seeders/UptLocationSeeder.php', 'w', encoding='utf-8') as f:
        f.write(seeder_code)
    print("Successfully wrote updated boundaries to database/seeders/UptLocationSeeder.php!")

