/**
 * AlfaFut Service Worker
 * Só guarda o que é público: arquivos estáticos e a página /offline.
 * Página autenticada, dado de turma e resposta de erro nunca ficam no aparelho.
 * - Navegação: sempre a rede; sem rede, a página /offline.
 * - Arquivos estaticos: stale-while-revalidate.
 * - O resto (API, dados ao vivo, POST): nem passa por aqui.
 */

const VERSAO = 'alfafut-v3';
const CACHE_ESTATICO = `${VERSAO}-estatico`;

const PRE_CACHE = [
    '/offline',
    '/favicon.svg',
    '/images/logo/icon.svg',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
    '/manifest.webmanifest',
];

const ESTATICOS = /^\/(build|images)\/|^\/favicon\.|^\/manifest\.webmanifest$/;

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
                    .filter((n) => n.startsWith('alfafut-') && n !== CACHE_ESTATICO)
                    .map((n) => caches.delete(n))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (req.mode === 'navigate') {
        event.respondWith(redeOuOffline(req));
        return;
    }

    if (ESTATICOS.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(req));
    }
});

async function redeOuOffline(req) {
    try {
        return await fetch(req);
    } catch (_) {
        const fallback = await caches.match('/offline');
        return fallback || new Response('Offline', { status: 503 });
    }
}

async function staleWhileRevalidate(req) {
    const cache = await caches.open(CACHE_ESTATICO);
    const cached = await cache.match(req);
    const fetchPromise = fetch(req)
        .then((r) => {
            if (r && r.ok) cache.put(req, r.clone()).catch(() => null);
            return r;
        })
        .catch(() => cached || new Response('Offline', { status: 503 }));
    return cached || fetchPromise;
}
