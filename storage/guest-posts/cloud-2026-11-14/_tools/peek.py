# Usage: python3 -I peek.py URL [keyword ...]  -> status, emails (incl. Cloudflare-protected), text around keywords
import sys, re, html, subprocess
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"
url=sys.argv[1]; keys=sys.argv[2:]
r=subprocess.run(["curl","-sSL","-A",UA,"--max-time","35","-w","\n__ST__%{http_code} %{url_effective}",url],capture_output=True,text=True,errors="replace")
b,_,st=r.stdout.rpartition("\n__ST__")
def dec(h):
    k=int(h[:2],16); return "".join(chr(int(h[i:i+2],16)^k) for i in range(2,len(h),2))
em=set(dec(m) for m in re.findall(r'data-cfemail="([0-9a-f]+)"',b))|set(re.findall(r"mailto:([^\"'?>\s]+)",b))|set(re.findall(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+\.[A-Za-z.]{2,}",re.sub(r"<[^>]+>"," ",b)))
em={e for e in em if not re.search(r"\.(png|jpg|jpeg|webp|svg|gif)$",e) and "sentry" not in e and "example" not in e}
t=re.sub(r"<script.*?</script>|<style.*?</style>|<noscript.*?</noscript>","",b,flags=re.S|re.I); t=html.unescape(re.sub(r"<[^>]+>"," ",t)); t=re.sub(r"\s+"," ",t)
print("##",st,"| chars",len(t)); print("EMAILS:",sorted(em)[:10])
seen=0
for k in keys:
    for m in list(re.finditer(re.escape(k),t,re.I))[:2]:
        print(" ["+k+"]",t[max(0,m.start()-170):m.start()+330]); seen+=1
if not keys: print(t[:1500])
links=[(html.unescape(re.sub(r"<[^>]+>","",x[1])).strip()[:40],x[0]) for x in re.findall(r'<a[^>]+href="([^"]+)"[^>]*>(.*?)</a>',b,re.S)]
want=[l for l in links if re.search(r"contribut|write for|guest|submit|editorial|contact|about|advertis",l[0]+" "+l[1],re.I)]
u=[]; [u.append(l) for l in want if l not in u]
print("LINKS:",u[:14])
