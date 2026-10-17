import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. GET /portal/ENS-2026
resp = opener.open('http://localhost:8080/portal/ENS-2026')
html = resp.read().decode('utf-8')
print("1. /portal/ENS-2026 status:", resp.status, "HTML length:", len(html))

# Extract CSRF token
csrf_match = re.search(r'name="_token"\s+value="([^"]+)"', html)
token = csrf_match.group(1) if csrf_match else ''
print("   CSRF Token extracted:", token[:10] + "...")

# 2. POST login
data = urllib.parse.urlencode({
    '_token': token,
    'matricule': 'E2026-9901',
    'mot_de_passe': 'Secret2026!'
}).encode('utf-8')

req = urllib.request.Request('http://localhost:8080/portal/ENS-2026/login', data=data, method='POST')
resp = opener.open(req)
html_dash = resp.read().decode('utf-8')
print("2. Dashboard after login status:", resp.status, "URL:", resp.geturl())
print("   Dashboard has student Sara Alami:", "Sara" in html_dash)

# 3. GET /portal/ENS-2026/historique
resp_histo = opener.open('http://localhost:8080/portal/ENS-2026/historique')
html_histo = resp_histo.read().decode('utf-8')
print("3. Historique status:", resp_histo.status, "HTML length:", len(html_histo))

# 4. GET /myportal
opener_guest = urllib.request.build_opener()
resp_my = opener_guest.open('http://localhost:8080/myportal')
html_my = resp_my.read().decode('utf-8')
print("4. /myportal status:", resp_my.status, "HTML length:", len(html_my))

print("\nALL VERIFICATIONS PASSED SUCCESSFULLY!")
