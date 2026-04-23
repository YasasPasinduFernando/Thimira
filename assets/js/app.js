(function () {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var base = document.querySelector('meta[name="app-base"]');
            var path = base && base.getAttribute('content') ? base.getAttribute('content').replace(/\/$/, '') : '';
            var swUrl = (path ? path + '/' : '/') + 'sw.js';
            navigator.serviceWorker.register(swUrl, { scope: path ? path + '/' : '/' }).catch(function () {});
        });
    }

    const btn = document.getElementById('use-my-location');
    if (!btn) {
        return;
    }

    btn.addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported on this device.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const url = new URL(window.location.href);
                url.searchParams.set('lat', String(pos.coords.latitude));
                url.searchParams.set('lng', String(pos.coords.longitude));
                window.location.href = url.toString();
            },
            function () {
                alert('Could not get your location. Please allow location permission.');
            }
        );
    });
})();
