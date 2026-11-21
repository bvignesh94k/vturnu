const { chromium } = require('playwright');
const checks = [
 ['Bizcommunity search','https://www.bizcommunity.com/Search/196/1/s-enquiry%20form.html'],
 ['The CEO Magazine search','https://www.theceomagazine.com/?s=Four+Numbers'],
 ['CommPRO search','https://www.commpro.biz/search?q=coverage+work'],
 ['IT News Africa search','https://www.itnewsafrica.com/?s=holds+the+keys'],
 ['The Media Online search','https://themediaonline.co.za/?s=AI+crawlers'],
 ['Passionate in Marketing search','https://www.passionateinmarketing.com/?s=95-5'],
 ['New Digital Age search','https://newdigitalage.co/?s=optimising+for+the+wrong+moment'],
 ['ManufacturingTomorrow home','https://www.manufacturingtomorrow.com/'],
 ['Medianews4u search','https://www.medianews4u.com/?s=consent+design+problem'],
 ['Medianews4u Authors Corner','https://www.medianews4u.com/category/authors-corner/'],
 ['Africa.com search','https://www.africa.com/?s=Selling+Beyond+Your+Borders'],
 ['CanadianSME search','https://canadiansme.ca/?s=digital+marketing+agency'],
 ['Digital Terminal search','https://digitalterminal.in/?s=Gmail'],
 ['Donorbox blog','https://donorbox.org/nonprofit-blog'],
 ['Techeconomy search','https://techeconomy.ng/?s=website+fast+enough'],
 ['CX Today search','https://www.cxtoday.com/?s=Vignesh'],
 ['UX Magazine search','https://uxmag.com/?s=Five+Readers'],
 ['Martechvibe search','https://martechvibe.com/?s=Vignesh'],
 ['CXM search','https://cxm.world/?s=Vignesh'],
 ['Frontier Enterprise search','https://www.frontier-enterprise.com/?s=Vignesh'],
 ['Management-Issues search','https://www.management-issues.com/?s=Vignesh'],
 ['Contact Center Pipeline search','https://www.contactcenterpipeline.com/?s=Vignesh'],
 ['SmallBusiness.co.uk search','https://smallbusiness.co.uk/?s=Vignesh'],
 ['HackerNoon profile','https://hackernoon.com/u/vigneshuday'],
 ['GrowthFolks blog','https://growthfolks.io/blog/'],
 ['TechJuice search','https://www.techjuice.pk/?s=signup+form'],
 ['Fibre2Fashion articles','https://www.fibre2fashion.com/industry-article/'],
 ['Call Centre Helper search','https://www.callcentrehelper.com/?s=campaign+brief'],
 ['Todays Hotelier search','https://todayshotelier.com/?s=direct+booking'],
];
const RE = /Sample Requests|Before Marketing Presses Send|One Question on Your Signup Form|Win the Direct Booking|Too Low for A\/B|Shippers Search by Lane|Vignesh|VTurnU|enquiry form|Four Numbers|Coverage Work|holds the keys|Block, allow or charge|95-5|wrong moment|RFQ Form|Consent Is Now a Design|Selling Beyond Your Borders|Hiring a Digital Marketing Agency|Email Delivery Is Now an IT Problem|Redesigning Your Nonprofit|Fast Enough for Your Customers|Search by Lane|Five Readers|Bots Are Filling/gi;
(async () => {
const browser = await chromium.launch();
const ctx = await browser.newContext({ userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36', viewport:{width:1280,height:900} });
for (const [label,url] of checks) {
  const page = await ctx.newPage();
  try {
    const r = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(3000);
    const info = await Promise.race([new Promise((_,rej)=>setTimeout(()=>rej(new Error('evaluate timeout')),15000)), page.evaluate((src) => {
      const re = new RegExp(src, 'gi');
      const t = (document.body ? document.body.innerText : '').replace(/\s+/g,' ');
      const hits=[]; let m;
      while ((m=re.exec(t)) && hits.length<4) hits.push(t.slice(Math.max(0,m.index-90), m.index+120));
      const links = Array.from(document.querySelectorAll('a[href*="vturnu"]')).map(a=>a.href+' rel='+a.getAttribute('rel')).slice(0,4);
      return {len:t.length, hits, links, head:t.slice(0,120)};
    }, RE.source)]);
    console.log(`\n=== ${label} | HTTP ${r&&r.status()} | ${page.url()}\n${JSON.stringify(info).slice(0,900)}`);
  } catch(e){ console.log(`\n=== ${label} ERROR ${e.message.slice(0,140)}`); }
  await Promise.race([page.close().catch(()=>{}), new Promise(r=>setTimeout(r,5000))]);
}
await browser.close();
})();
