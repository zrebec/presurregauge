// Based on https://developers.google.com/web/fundamentals/primers/service-workers
const CACHE_NAME = 'pressure-gauge-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/style.css',
  '/favicon.svg',
  '/favicon-192.png',
  '/favicon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
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