self.addEventListener("install", function(event) {
    event.waitUntil(
        caches.open("sw-cache").then((cache) => cache.add("index.html"))
    );
});
    
self.addEventListener("fetch", function(event) {
    event.respondWith(
        caches.match(event.request).then((response) => response || fetch(event.request))
    );
});