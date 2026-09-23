console.log('LE ROY Service Worker cargado');


self.addEventListener('install', event => {

    console.log('LE ROY SW: INSTALL');

    self.skipWaiting();

});


self.addEventListener('activate', event => {

    console.log('LE ROY SW: ACTIVATE');

    event.waitUntil(
        self.clients.claim()
    );

});


self.addEventListener('fetch', event => {

    event.respondWith(
        fetch(event.request)
            .catch(() => {

                return new Response(
                    'Sin conexión',
                    {
                        status: 503,
                        headers: {
                            'Content-Type': 'text/plain'
                        }
                    }
                );

            })
    );

});