const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const ctx = await b.newContext({ userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36', viewport:{width:1280,height:1000} });
  const p = await ctx.newPage();
  await p.goto(process.argv[2], { waitUntil: 'domcontentloaded', timeout: 60000 }); await p.waitForTimeout(3500);
  const info = await p.evaluate(() => {
    const forms = Array.from(document.querySelectorAll('form')).map(f => ({ id: f.id, action: f.action, fields: Array.from(f.querySelectorAll('input,textarea,select,button')).map(e => ({ tag: e.tagName, type: e.type, name: e.name, id: e.id, req: e.required || e.getAttribute('aria-required'), vis: e.offsetParent !== null, label: (document.querySelector('label[for="' + e.id + '"]') || {}).innerText || '', opts: e.tagName === 'SELECT' ? e.options.length : undefined })) }));
    const captcha = !!document.querySelector('.g-recaptcha, iframe[src*="recaptcha"], iframe[src*="hcaptcha"], .cf-turnstile, iframe[src*="turnstile"]');
    return { forms: forms.filter(f => f.fields.length > 3), captcha };
  });
  for (const f of info.forms) { console.log('FORM', f.id, f.action); for (const x of f.fields) if (x.type !== 'hidden') console.log('  ', x.tag, x.type, '|', x.name, '|', x.id, '| req:', x.req, '| vis:', x.vis, '|', (x.label || '').replace(/\s+/g, ' ').slice(0, 60), x.opts ? '| options ' + x.opts : ''); }
  console.log('CAPTCHA:', info.captcha);
  await p.screenshot({ path: 'pages/form.png', fullPage: false });
  await b.close();
})();
