# Usage: python3 -I readpage.py URL "start phrase" [chars]  -> page text from the phrase, Cloudflare emails decoded inline, form fields listed
import sys, re, html, subprocess
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"
url=sys.argv[1]; start=sys.argv[2] if len(sys.argv)>2 else ""; n=int(sys.argv[3]) if len(sys.argv)>3 else 2500
r=subprocess.run(["curl","-sSL","-A",UA,"--max-time","35","-w","\n__ST__%{http_code} %{url_effective}",url],capture_output=True,text=True,errors="replace")
b,_,st=r.stdout.rpartition("\n__ST__")
def dec(h):
    k=int(h[:2],16); return "".join(chr(int(h[i:i+2],16)^k) for i in range(2,len(h),2))
b=re.sub(r'<a[^>]*href="/cdn-cgi/l/email-protection#([0-9a-f]+)"[^>]*>.*?</a>',lambda m:" "+dec(m.group(1))+" ",b,flags=re.S)
b=re.sub(r'<(?:a|span)[^>]*data-cfemail="([0-9a-f]+)"[^>]*>.*?</(?:a|span)>',lambda m:" "+dec(m.group(1))+" ",b,flags=re.S)
b=re.sub(r'<a[^>]*href="mailto:([^"?]+)[^"]*"[^>]*>(.*?)</a>',lambda m:" "+re.sub(r"<[^>]+>","",m.group(2))+" <"+m.group(1)+"> ",b,flags=re.S)
forms=[]
for f in re.findall(r"<form[^>]*>(.*?)</form>",b,re.S):
    fl=re.findall(r'<(?:input|textarea|select)[^>]*name="([^"]+)"[^>]*>',f); lb=[html.unescape(re.sub(r"<[^>]+>","",l)).strip() for l in re.findall(r"<label[^>]*>(.*?)</label>",f,re.S)]
    if len(fl)>2: forms.append((fl[:25],[l for l in lb if l][:25]))
t=re.sub(r"<script.*?</script>|<style.*?</style>|<noscript.*?</noscript>","",b,flags=re.S|re.I); t=html.unescape(re.sub(r"<[^>]+>"," ",t)); t=re.sub(r"\s+"," ",t)
i=t.find(start) if start else 0
print("##",st,"| chars",len(t)); print(t[max(0,i):max(0,i)+n] if i>=0 else "(start phrase not found) "+t[:600])
for fl,lb in forms[:2]: print("FORM fields:",fl); print("  labels:",lb)
print("iframes:",re.findall(r'<iframe[^>]+src="([^"]+)"',b)[:4])
