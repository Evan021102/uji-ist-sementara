// sw.js - Service Worker untuk PWA
const CACHE_NAME = 'psikotes-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/sesi2.php',
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

// Fetch: network-first strategy (penting untuk ujian real-time)
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests atau API calls
  if (event.request.method !== 'GET' || event.request.url.includes('sesi')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkRes) => {
        // Update cache dengan response terbaru
        const resClone = networkRes.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, resClone);
        });
        return networkRes;
      })
      .catch(() => {
        // Fallback ke cache jika offline
        return caches.match(event.request);
      })
  );
});

// Optional: Background sync untuk submit jawaban (jika offline)
// self.addEventListener('sync', (event) => { ... });