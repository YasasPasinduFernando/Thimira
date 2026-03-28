(function () {
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
