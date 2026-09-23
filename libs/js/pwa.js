console.log('LE ROY PWA JS cargado');

if ('serviceWorker' in navigator) {

    console.log('El navegador soporta Service Worker');

    window.addEventListener('load', function () {

        navigator.serviceWorker.register('./sw.js')
            .then(function (registration) {

                console.log('✓ Service Worker registrado');
                console.log('Scope:', registration.scope);

            })
            .catch(function (error) {

                console.error('✗ Error registrando Service Worker');
                console.error(error);

            });

    });

} else {

    console.error(
        '✗ Este navegador NO soporta Service Worker'
    );

}   