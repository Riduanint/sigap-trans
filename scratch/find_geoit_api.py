import urllib.request, re

url = 'https://batas-admin.geoit.dev/_next/static/chunks/pages/index-d8a1224fd8f88336.js'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=15) as resp:
        content = resp.read().decode('utf-8')
        urls = re.findall(r'https?://[^\s"\'<>]+', content)
        print('Found URLs:', set(urls))
        apis = re.findall(r'/[a-zA-Z0-9_\-/]+', content)
        matches = [a for a in apis if any(k in a for k in ['desa', 'admin', 'download', 'search', 'kel'])]
        print('Found matching paths:', set(matches[:20]))
except Exception as e:
    print('Error:', e)
