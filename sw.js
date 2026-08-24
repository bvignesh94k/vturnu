/* VTurnU service worker: offline support + faster repeat visits.
 * Strategy:
 *   navigations   → network-first, fall back to cache, then the offline page
 *   static assets → stale-while-revalidate (instant, refreshed in background)
 * Never touches admin, form posts, or stored lead data.
 */
const VERSION = 'vturnu-v2';
const PRECACHE = `${VERSION}-precache`;
const RUNTIME = `${VERSION}-runtime`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/assets/js/main.js',
    '/assets/img/vturnu-icon-mark.svg',
    '/assets/img/vturnu-icon-mark.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE)
            // Individual failures must not abort the whole install
            .then((cache) => Promise.allSettled(PRECACHE_URLS.map((u) => cache.add(u))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

/** Paths the worker must never cache or interfere with. */
function isExcluded(url) {
    return /^\/(admin|enquiry|storage|includes)(\/|$)/.test(url.pathname);
}

function isAsset(url) {
    return /\.(css|js|png|jpe?g|svg|webp|avif|woff2?|ico)$/i.test(url.pathname);
}

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const sameOrigin = url.origin === self.location.origin;
    if (sameOrigin && isExcluded(url)) return;

    // HTML navigations: always try the network first so content stays fresh.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(RUNTIME).then((c) => c.put(req, copy));
                    return res;
                })
                .catch(() => caches.match(req).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Static assets (incl. Google Fonts): serve from cache, refresh behind the scenes.
    if (isAsset(url) || url.hostname.endsWith('gstatic.com') || url.hostname.endsWith('googleapis.com')) {
        event.respondWith(
            caches.match(req).then((hit) => {
                const network = fetch(req)
                    .then((res) => {
                        if (res && (res.ok || res.type === 'opaque')) {
                            const copy = res.clone();
                            caches.open(RUNTIME).then((c) => c.put(req, copy));
                        }
                        return res;
                    })
                    .catch(() => hit);
                return hit || network;
            })
        );
    }
});

// Lets the page trigger an immediate update instead of waiting for a reload.
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') self.skipWaiting();
});
