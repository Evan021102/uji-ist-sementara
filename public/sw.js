// sw.js - Service Worker untuk PWA
const CACHE_NAME = 'psikotes-v1';
const ASSETS_TO_CACHE = [
  '/assets/icon-192.png',
  '/assets/icon-512.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap'
];

// Install: cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('📦 Caching assets');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate: cleanup old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => 
      Promise.all(
        keys.filter(key => key !== CACHE_NAME)
            .map(key => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch: only intercept and cache static assets; bypass all dynamic document routes
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  const url = new URL(event.request.url);

  // List of dynamic paths that should NEVER be cached
  if (
    url.pathname === '/' ||
    url.pathname.startsWith('/ujian') ||
    url.pathname.startsWith('/dashboard') ||
    url.pathname.startsWith('/login') ||
    url.pathname.startsWith('/logout')
  ) {
    return;
  }

  // Only intercept static assets (images, stylesheets, scripts, fonts)
  const isStaticAsset = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|json)$/i);

  if (!isStaticAsset) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((cachedRes) => {
        if (cachedRes) {
          // Serve from cache, but fetch fresh in background to update cache
          fetch(event.request).then((networkRes) => {
            if (networkRes.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkRes));
            }
          }).catch(() => {});
          return cachedRes;
        }

        return fetch(event.request).then((networkRes) => {
          if (networkRes.status === 200) {
            const resClone = networkRes.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, resClone));
          }
          return networkRes;
        });
      })
  );
});

// Optional: Background sync untuk submit jawaban (jika offline)
// self.addEventListener('sync', (event) => { ... });