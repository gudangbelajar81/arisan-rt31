const CACHE_NAME = 'arisan-rt31-cache-v3';
const staticAssets = [
  './',
  'index.php',
  'style.css',
  'logo_m.png',
  'offline.html',
  'manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(staticAssets)));
});

self.addEventListener('activate', event => {
  // Hapus cache versi lama agar tidak memakan memori HP
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;
  
  // Jika ini halaman utama atau file statis, gunakan strategi "Stale-While-Revalidate"
  // Artinya: Tampilkan data dari Cache HP secara instan (0 detik), lalu peladen diperbarui di latar belakang.
  if (req.url.includes('logo_m.png') || req.url.includes('style.css') || req.url.includes('index.php') || req.url.endsWith('/')) {
      event.respondWith(
          caches.match(req).then(cachedRes => {
              const fetchPromise = fetch(req).then(networkRes => {
                  caches.open(CACHE_NAME).then(cache => cache.put(req, networkRes.clone()));
                  return networkRes;
              }).catch(() => {});
              return cachedRes || fetchPromise;
          })
      );
  } else {
      // Halaman dinamis (daftar.php, peserta.php) wajib Network First agar data arisan tidak basi
      event.respondWith(
          fetch(req).catch(() => {
              return caches.match(req).then(res => res || caches.match('offline.html'));
          })
      );
  }
});
