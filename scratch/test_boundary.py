import urllib.request, urllib.parse, json

query = """
[out:json][timeout:30];
area["name"="Kalimantan Selatan"]["admin_level"="4"]->.kalsel;
(
  relation["admin_level"="5"](area.kalsel);
);
out tags;
"""

url = 'https://overpass-api.de/api/interpreter'
data = urllib.parse.urlencode({'data': query}).encode('utf-8')
req = urllib.request.Request(url, data=data, headers={'User-Agent': 'SIGAP-TRANS/1.0'})

try:
    with urllib.request.urlopen(req, timeout=35) as resp:
        res = json.loads(resp.read().decode('utf-8'))
        print(f"Total relations: {len(res.get('elements', []))}")
        for el in res.get('elements', []):
            print(el.get('id'), ':', el.get('tags', {}).get('name'))
except Exception as e:
    print('Error:', e)
