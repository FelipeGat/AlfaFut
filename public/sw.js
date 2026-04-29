/**
 * AlfaFut Service Worker
 * Estrategia: network-first com fallback offline cache.
 * - HTML: tenta a rede; em offline serve a pagina /offline.
 * - Assets estaticos (CSS/JS/img/font): stale-while-revalidate.
 * - APIs (POST/PATCH/DELETE): so rede (nao cacheia).
 */

const VERSAO = 'alfafut-v1';
const CACHE_ESTATICO = `${VERSAO}-estatico`;
const CACHE_DINAMICO = `${VERSAO}-dinamico`;

const PRE_CACHE = [
    '/',
    '/offline',
    '/favicon.svg',
    '/images/logo/icon.svg',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_ESTATICO).then((cache) =>
            cache.addAll(PRE_CACHE).catch(() => null)
        ).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((nomes) =>
            Promise.all(
                nomes
                    .filter((n) => n.startsWith('alfafut-') && !n.startsWith(VERSAO))
                    .map((n) => caches.delete(n))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // So GET passa pelo SW
    if (req.method !== 'GET') return;

    // Ignora chrome-extension, ws etc
    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Documento HTML: network-first com fallback /offline
    if (req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(networkFirstHtml(req));
        return;
    }

    // Demais GETs: stale-while-revalidate
    event.respondWith(staleWhileRevalidate(req));
});

async function networkFirstHtml(req) {
    try {
        const r = await fetch(req);
        const cache = await caches.open(CACHE_DINAMICO);
        cache.put(req, r.clone()).catch(() => null);
        return r;
    } catch (_) {
        const cache = await caches.open(CACHE_DINAMICO);
        const cached = await cache.match(req);
        if (cached) return cached;
        const fallback = await caches.match('/offline');
        return fallback || new Response('Offline', { status: 503 });
    }
}

async function staleWhileRevalidate(req) {
    const cache = await caches.open(CACHE_DINAMICO);
    const cached = await cache.match(req);
    const fetchPromise = fetch(req)
        .then((r) => {
            if (r && r.ok) cache.put(req, r.clone()).catch(() => null);
            return r;
        })
        .catch(() => cached || new Response('Offline', { status: 503 }));
    return cached || fetchPromise;
}
