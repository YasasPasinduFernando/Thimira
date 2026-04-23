/* Village Traveler — minimal offline-friendly shell (static assets only; PHP pages always use network). */
const CACHE_NAME = 'village-traveler-v1';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            const scope = self.registration.scope;
            const urls = [
                scope + 'assets/css/app.css',
                scope + 'assets/js/app.js',
                scope + 'assets/favicon.svg',
                scope + 'assets/icons/icon-192.png',
                scope + 'assets/icons/icon-512.png',
                scope + 'manifest.php',
            ];
            return cache.addAll(urls.map((u) => new Request(u, { cache: 'reload' }))).then(() => self.skipWaiting());
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }
    const url = new URL(event.request.url);
    const path = url.pathname;

    if (path.endsWith('.php') && !path.endsWith('manifest.php')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }
            return fetch(event.request)
                .then((response) => {
                    if (response && response.status === 200) {
                        const type = response.headers.get('content-type') || '';
                        if (!type.includes('text/html')) {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                        }
                    }
                    return response;
                })
                .catch(function () {
                    return caches.match(event.request);
                });
        })
    );
});
