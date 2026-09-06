const CACHE_NAME = 'more-tablet-v3';
const STATIC_ASSETS = [
  '/tablet',
  '/manifest.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/apple-touch-icon.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_ASSETS).catch(err => console.warn('PWA caching non-fatal warning:', err));
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  // Pass through non-GET requests immediately
  if (event.request.method !== 'GET') {
    return;
  }

  // Network first with cache fallback for smooth kiosk operation
  event.respondWith(
    fetch(event.request)
      .then(networkResponse => {
        // Cache successful responses for tablet pages
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      })
      .catch(() => {
        return caches.match(event.request).then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }
          if (event.request.mode === 'navigate') {
            return caches.match('/tablet');
          }
        });
      })
  );
});
