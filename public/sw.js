const STATIC_CACHE = 'edunjobi-static-v6';
const DYNAMIC_CACHE = 'edunjobi-dynamic-v6';

// Offline queue (IndexedDB)
const IDB_NAME = 'edunjobi-pwa';
const IDB_VERSION = 1;
const QUEUE_STORE = 'request_queue';

function openDb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(IDB_NAME, IDB_VERSION);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(QUEUE_STORE)) {
        db.createObjectStore(QUEUE_STORE, { keyPath: 'id', autoIncrement: true });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

async function queueRequest(entry) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readwrite');
    tx.objectStore(QUEUE_STORE).add(entry);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}

async function getQueuedRequests() {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readonly');
    const req = tx.objectStore(QUEUE_STORE).getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror = () => reject(req.error);
  });
}

async function deleteQueuedRequest(id) {
  const db = await openDb();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(QUEUE_STORE, 'readwrite');
    tx.objectStore(QUEUE_STORE).delete(id);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
}

async function replayQueue() {
  const items = await getQueuedRequests();
  for (const item of items) {
    try {
      const headers = new Headers(item.headers || {});
      // Ensure JSON requests still work on replay
      if (item.body && !headers.get('Content-Type')) {
        headers.set('Content-Type', 'application/json');
      }
      headers.set('X-Requested-With', headers.get('X-Requested-With') || 'XMLHttpRequest');

      const res = await fetch(item.url, {
        method: item.method,
        headers,
        body: item.body ?? undefined,
        credentials: 'same-origin',
      });

      if (res.ok) {
        await deleteQueuedRequest(item.id);
      }
    } catch (e) {
      // Still offline or server error; keep queued
    }
  }
}

const urlsToCache = [
  '/',
  '/admin/dashboard',
  '/admin/pos',
  '/admin/hotel-pos',
  '/admin/rooms',
  '/admin/room-bookings',
  '/admin/kitchen',
  '/manifest.json',
  '/offline.html',
  '/sash/assets/css/style.css',
  '/sash/assets/plugins/bootstrap/css/bootstrap.min.css',
  '/sash/assets/css/icons.css',
  '/sash/assets/css/dark-style.css',
  '/sash/assets/js/jquery.min.js',
  '/sash/assets/plugins/bootstrap/js/bootstrap.bundle.min.js',
  '/sash/assets/js/custom.js',
  '/logo.jpg'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  console.log('[ServiceWorker] Install');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[ServiceWorker] Caching app shell');
        return cache.addAll(urlsToCache).catch((err) => {
          console.log('[ServiceWorker] Cache addAll failed:', err);
          // Cache what we can, don't fail the install
          return Promise.all(
            urlsToCache.map(url => {
              return cache.add(url).catch(err => {
                console.log(`[ServiceWorker] Failed to cache ${url}:`, err);
              });
            })
          );
        });
      })
  );
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Fetch event - cache-first for static GET, offline queue for same-origin mutations
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Generic offline queue for same-origin non-GET requests
  if (request.method !== 'GET' && url.origin === location.origin) {
    const path = url.pathname;
    const method = request.method.toUpperCase();

    // Do not queue auth/logout/password flows
    const denyList = [
      '/login',
      '/logout',
      '/register',
      '/forgot-password',
      '/reset-password',
      '/email/verification-notification',
    ];
    if (denyList.includes(path)) return;

    // Skip file uploads / multipart bodies
    const contentType = request.headers.get('Content-Type') || '';
    if (contentType.includes('multipart/form-data')) return;

    const shouldQueue = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);
    if (!shouldQueue) return;

    event.respondWith((async () => {
      try {
        // Network first when online
        return await fetch(request.clone());
      } catch (e) {
        const cloned = request.clone();
        let body = null;
        try {
          body = await cloned.text();
        } catch (_) {}

        const headersObj = {};
        for (const [k, v] of cloned.headers.entries()) headersObj[k] = v;

        await queueRequest({
          url: url.pathname + url.search,
          method,
          headers: headersObj,
          body,
          queued_at: Date.now(),
        });

        // Try Background Sync if available
        try {
          if ('sync' in self.registration) {
            await self.registration.sync.register('sync-edunjobi-queue');
          }
        } catch (_) {}

        const acceptsJson = (request.headers.get('Accept') || '').includes('application/json') ||
          (request.headers.get('X-Requested-With') || '') === 'XMLHttpRequest';

        // If this was a fetch/XHR request, return JSON; otherwise return an offline page.
        if (acceptsJson) {
          return new Response(JSON.stringify({
            success: false,
            queued: true,
            message: 'Saved offline. Will sync when internet is back.',
          }), { status: 202, headers: { 'Content-Type': 'application/json' } });
        }

        return caches.match('/offline.html');
      }
    })());

    return;
  }

  // Skip external requests
  if (url.origin !== location.origin) return;

  // Letters / PDFs: always hit network — SW cache-first path can serve stale login HTML
  // for this URL after auth was removed from the route (same cache key as the PDF request).
  const pathname = url.pathname;
  if (pathname.startsWith('/letters/') || (request.method === 'GET' && pathname.endsWith('.pdf'))) {
    event.respondWith(fetch(request));
    return;
  }

  // For GET navigations/documents: network-first, fallback to cache/offline.html
  if (request.method === 'GET' && request.destination === 'document') {
    event.respondWith((async () => {
      try {
        const res = await fetch(request);
        // Cache successful HTML pages
        if (res && res.ok) {
          const copy = res.clone();
          const cache = await caches.open(DYNAMIC_CACHE);
          cache.put(request, copy);
        }
        return res;
      } catch (e) {
        const cached = await caches.match(request);
        return cached || (await caches.match('/offline.html'));
      }
    })());
    return;
  }

  event.respondWith(
    caches.match(request)
      .then((cachedResponse) => {
        // Return cached version if available
        if (cachedResponse) {
          return cachedResponse;
        }

        // Fetch from network
        return fetch(request)
          .then((response) => {
            // Don't cache if not a valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            const responseToCache = response.clone();

            // Cache in dynamic cache
            caches.open(DYNAMIC_CACHE)
              .then((cache) => {
                cache.put(request, responseToCache);
              });

            return response;
          })
          .catch(() => {
            // If both cache and network fail, return offline fallback
            if (request.destination === 'document') {
              return caches.match('/offline.html');
            }
            // For images, return a placeholder if available
            if (request.destination === 'image') {
              return caches.match('/logo.jpg');
            }
          });
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[ServiceWorker] Activate');
  const cacheWhitelist = [STATIC_CACHE, DYNAMIC_CACHE];

  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            console.log('[ServiceWorker] Removing old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // Take control of all pages immediately
  return self.clients.claim();
});

// Background sync: replay queued requests
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-edunjobi-queue') {
    event.waitUntil(replayQueue());
  }
});

// Allow the page to trigger replay
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SYNC_QUEUE') {
    event.waitUntil(replayQueue());
  }
});

// Handle push notifications (for future use)
self.addEventListener('push', (event) => {
  console.log('[ServiceWorker] Push notification received');
  // You can add push notification handling here
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  console.log('[ServiceWorker] Notification click received');
  event.notification.close();
  // You can add navigation logic here
});
