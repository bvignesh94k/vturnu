// Connect the website to the connect@vturnu.com Google Calendar.
//
// The booking system (includes/booking.php) reads free/busy times and creates
// events with Google Meet links as connect@vturnu.com. It signs in with an
// OAuth client of the "VTurnU Website" Google Cloud project (Internal app, so
// only vturnu.com accounts can use it and the access does not expire after a
// week) and a refresh token that this script obtains once.
//
//   node bin/google-connect.mjs <client_secret.json> [--env-out <file>] [--no-vercel]
//
// 1. Download the Desktop app client's JSON from Google Cloud Console.
// 2. Run this script. It prints a Google sign-in link and waits.
// 3. Open the link, sign in as connect@vturnu.com and press Allow.
// 4. The script checks the calendar it reached and stores GOOGLE_CLIENT_ID,
//    GOOGLE_CLIENT_SECRET and GOOGLE_REFRESH_TOKEN as sensitive Production
//    environment variables in Vercel (through stdin, never on screen or in git).
//    --env-out also writes them to a local file for testing; delete it after.
//
// Redeploy afterwards so the new variables reach the site. Run it again any
// time the connection breaks (for example if access is removed in the Google
// account's security settings).
import { spawn } from 'node:child_process';
import crypto from 'node:crypto';
import fs from 'node:fs';
import http from 'node:http';

const SCOPES = [
  'https://www.googleapis.com/auth/calendar.events',
  'https://www.googleapis.com/auth/calendar.freebusy',
];
const EXPECT = 'connect@vturnu.com';

const args = process.argv.slice(2);
const file = args.find((a) => !a.startsWith('--'));
const envOut = args.includes('--env-out') ? args[args.indexOf('--env-out') + 1] : '';
const toVercel = !args.includes('--no-vercel');
if (!file || !fs.existsSync(file)) {
  console.error('Usage: node bin/google-connect.mjs <client_secret.json> [--env-out <file>] [--no-vercel]');
  process.exit(2);
}
const json = JSON.parse(fs.readFileSync(file, 'utf8'));
const client = json.installed ?? json.web ?? json;
if (!client.client_id || !client.client_secret) {
  console.error('That file has no client_id and client_secret.');
  process.exit(2);
}

const b64url = (buf) => buf.toString('base64').replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
const verifier = b64url(crypto.randomBytes(48));
const challenge = b64url(crypto.createHash('sha256').update(verifier).digest());
const state = b64url(crypto.randomBytes(18));

function setVercel(name, value) {
  return new Promise((ok, bad) => {
    /* One command string: the name is one of the three fixed names below, and the value goes through stdin. */
    const p = spawn(`npx -y vercel@59.1.4 env add ${name} production --sensitive --force`, { shell: true, stdio: ['pipe', 'pipe', 'pipe'] });
    let out = '';
    p.stdout.on('data', (d) => { out += d; });
    p.stderr.on('data', (d) => { out += d; });
    p.on('exit', (code) => (code === 0 ? ok() : bad(new Error(`vercel env add ${name} failed: ${out.replace(value, '[hidden]').slice(-400)}`))));
    p.stdin.end(value);
  });
}

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://127.0.0.1');
  if (url.pathname !== '/oauth2callback') { res.writeHead(404); res.end(); return; }
  const finish = (status, text) => {
    res.writeHead(status, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(`<!doctype html><meta charset="utf-8"><title>VTurnU calendar</title><p style="font:18px system-ui;padding:40px;max-width:640px">${text}</p>`);
  };
  try {
    if (url.searchParams.get('state') !== state) throw new Error('The sign-in answer did not match this request. Run the script again.');
    if (url.searchParams.get('error')) throw new Error('Google said: ' + url.searchParams.get('error'));
    const tokenRes = await fetch('https://oauth2.googleapis.com/token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        code: url.searchParams.get('code') ?? '', client_id: client.client_id, client_secret: client.client_secret,
        redirect_uri: redirect, grant_type: 'authorization_code', code_verifier: verifier,
      }),
    });
    const tokens = await tokenRes.json();
    if (!tokenRes.ok || !tokens.refresh_token) throw new Error('No refresh token came back: ' + (tokens.error_description ?? tokens.error ?? tokenRes.status));
    const granted = String(tokens.scope ?? '').split(' ');
    const missing = SCOPES.filter((s) => !granted.includes(s));
    if (missing.length) throw new Error('These permissions were not granted: ' + missing.join(', ') + '. Run again and tick every box.');
    /* Which calendar did we reach? A primary calendar's summary is the account's address. */
    const cal = await fetch('https://www.googleapis.com/calendar/v3/calendars/primary/events?maxResults=1', {
      headers: { Authorization: 'Bearer ' + tokens.access_token },
    }).then((r) => r.json());
    const who = String(cal.summary ?? '');
    if (who.toLowerCase() !== EXPECT) throw new Error(`Signed in as ${who || 'an unknown account'}, not ${EXPECT}. Run again and choose ${EXPECT}.`);

    const vars = { GOOGLE_CLIENT_ID: client.client_id, GOOGLE_CLIENT_SECRET: client.client_secret, GOOGLE_REFRESH_TOKEN: tokens.refresh_token };
    if (envOut) {
      fs.writeFileSync(envOut, Object.entries(vars).map(([k, v]) => `${k}=${v}`).join('\n') + '\n', { mode: 0o600 });
    }
    if (toVercel) {
      for (const [k, v] of Object.entries(vars)) await setVercel(k, v);
    }
    console.log(`Connected to ${who}.` + (toVercel ? ' GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET and GOOGLE_REFRESH_TOKEN are set in Vercel (Production, sensitive). Redeploy to use them.' : '')
      + (envOut ? ` Local copy: ${envOut}` : ''));
    finish(200, `Done. The website can now use the ${who} calendar. You can close this tab.`);
    server.close();
  } catch (e) {
    console.error(String(e.message ?? e));
    finish(400, String(e.message ?? e).replace(/</g, '&lt;'));
    server.close();
    process.exitCode = 1;
  }
});

let redirect = '';
server.listen(0, '127.0.0.1', () => {
  redirect = `http://127.0.0.1:${server.address().port}/oauth2callback`;
  const auth = new URL('https://accounts.google.com/o/oauth2/v2/auth');
  auth.search = new URLSearchParams({
    client_id: client.client_id, redirect_uri: redirect, response_type: 'code', scope: SCOPES.join(' '),
    access_type: 'offline', prompt: 'consent', login_hint: EXPECT, state, code_challenge: challenge, code_challenge_method: 'S256',
  }).toString();
  console.log('Open this link, sign in as ' + EXPECT + ' and press Allow:\n' + auth.toString());
});
