// DocScolaire SaaS — Service Worker minimal pour PWA
// Permet l'ajout sur l'écran d'accueil et le cache des ressources statiques

const CACHE_NAME = 'docscolaire-v4';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Silently fail - les ressources seront mises en cache au fur et à mesure
            });
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Stratégie Network First pour toutes les requêtes
// (garantit les données fraîches pour les documents)
self.addEventListener('fetch', (event) => {
    // Ignorer les requêtes non-GET et les APIs
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/download')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Mettre en cache les ressources statiques uniquement
                if (response.ok && event.request.url.match(/\.(css|js|png|jpg|svg|woff2?)$/)) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
