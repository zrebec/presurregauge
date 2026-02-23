// Based on https://developers.google.com/web/fundamentals/primers/service-workers
const CACHE_NAME = 'pressure-gauge-v2';
const urlsToCache = [
  '/',
  '/index.php',
  '/style.css',
  '/assets/icons/favicon.svg',
  '/assets/icons/favicon-192.png',
  '/assets/icons/favicon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(names =>
            Promise.all(names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n)))
        )
    );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});
