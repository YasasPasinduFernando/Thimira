(function () {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var base = document.querySelector('meta[name="app-base"]');
            var path = base && base.getAttribute('content') ? base.getAttribute('content').replace(/\/$/, '') : '';
            var swUrl = (path ? path + '/' : '/') + 'sw.js';
            navigator.serviceWorker.register(swUrl, { scope: path ? path + '/' : '/' }).catch(function () {});
        });
    }
})();
