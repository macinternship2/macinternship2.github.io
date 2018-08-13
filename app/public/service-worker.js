var cacheName = 'v1';

var cacheFiles = [];

self.addEventListener('install', function(e) {   
    try {
        e.waitUntil(
            caches.open(cacheName).then(function(cache) {
                return cache.addAll(cacheFiles);
            })
        )
    } catch(e) {
        console.log(e);
    }
    
})  

self.addEventListener('activate', function(e) {
    e.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(cacheNames.map(function(thisCacheName) {

                if (thisCacheName !== cacheName) {
                    return caches.delete(thisCacheName);
                }
            }));
        })
    );
    e.waitUntil(

        caches.keys().then(function(cacheNames) {
            return Promise.all(cacheNames.map(function(thisCacheName) {

                if (thisCacheName !== cacheName) {
                    return caches.delete(thisCacheName);
                }
            }));
        })
    );
})

self.addEventListener('fetch',function(e) {
   
    if (e.request.url.indexOf('https://maps.googleapi.com') !== 0 ||
     e.request.url.indexOf('http://maps.googleapis.com') !== 0) {
        e.respondWith(
            caches.match(e.request)
                .then(function(response) {

                    if (response) {
                        return response;
                    }

                    var requestClone = e.request.clone();
                    return fetch(requestClone)
                        .then(function(response) {

                            if (!response) {
                                return response;
                            }

                            var responseClone = response.clone();
                            caches.open(cacheName).then(function(cache) {
                                cache.put(e.request, responseClone)
                                .catch(function(err) {
                                    console.log('Post Request Made', err);
                                });     
                                return response;
                            });

                        })
                        .catch(function(err) {
                            console.log('[ServiceWorker] Error Fetching & Caching New Data', err);
                        });
                })
        );
  }
})