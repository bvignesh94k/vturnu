import sys, re, json, subprocess, html
from html.parser import HTMLParser
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"
def fetch(url, out=None):
    r = subprocess.run(["curl","-sSL","-A",UA,"--max-time","40","-w","\n__STATUS__%{http_code} %{url_effective}",url],capture_output=True,text=True,errors="replace")
    body, _, st = r.stdout.rpartition("\n__STATUS__")
    if out:
        open(out,"w").write(body)
    return body, st
class P(HTMLParser):
    def __init__(s, needle):
        super().__init__(); s.links=[]; s.cur=None; s.meta={}; s.title=""; s.intitle=False; s.needle=needle
    def handle_starttag(s, t, a):
        a=dict(a)
        if t=="a":
            href=a.get("href") or ""
            if s.needle in href.lower():
                s.cur={"href":href,"rel":a.get("rel"),"target":a.get("target"),"text":""}
        if t=="meta":
            k=a.get("property") or a.get("name")
            if k and k.lower() in ("og:title","og:image","og:image:width","og:image:height","article:published_time","author","description","og:type","article:modified_time"):
                s.meta[k.lower()]=a.get("content")
        if t=="title": s.intitle=True
    def handle_endtag(s,t):
        if t=="a" and s.cur is not None:
            s.cur["text"]=re.sub(r"\s+"," ",s.cur["text"]).strip(); s.links.append(s.cur); s.cur=None
        if t=="title": s.intitle=False
    def handle_data(s,d):
        if s.cur is not None: s.cur["text"]+=d
        if s.intitle: s.title+=d
if __name__=="__main__":
    url=sys.argv[1]; needle=(sys.argv[2] if len(sys.argv)>2 else "vturnu").lower(); out=sys.argv[3] if len(sys.argv)>3 else None
    body, st = fetch(url, out)
    p=P(needle); p.feed(body)
    txt=re.sub(r"<script.*?</script>|<style.*?</style>","",body,flags=re.S|re.I)
    txt=html.unescape(re.sub(r"<[^>]+>"," ",txt)); txt=re.sub(r"\s+"," ",txt)
    hits=[m.start() for m in re.finditer(r"Vignesh|VTurnU",txt)]
    print(json.dumps({"status":st,"title":p.title.strip(),"meta":p.meta,"links":p.links,"bytes":len(body),
      "mentions":[txt[max(0,i-80):i+120] for i in hits[:6]]},indent=1,ensure_ascii=False))
