(function (global) {
    'use strict';

    var state = 'idle';
    var clients = [];
    var failure = '';
    var timeout = null;

    function fail(message) {
        if (state === 'failed') return;
        state = 'failed';
        failure = message;
        global.clearTimeout(timeout);
        clients.forEach(function (client) { client.error(message); });
    }

    function ready(client) {
        try {
            client.ready(global.google.maps);
        } catch (error) {
            client.error('Google Maps не може да бъде инициализирана.');
            global.console.error(error);
        }
    }

    function load(config, onReady, onError) {
        var client = { ready: onReady, error: onError };
        if (state === 'failed') {
            onError(failure);
            return;
        }
        clients.push(client);
        if (state === 'ready') {
            ready(client);
            return;
        }
        if (state === 'loading') return;
        if (!config.apiKey && !config.allowKeylessDev) {
            fail('Не е настроен ключ за Google Maps.');
            return;
        }

        state = 'loading';
        var previousAuthFailure = global.gm_authFailure;
        global.gm_authFailure = function () {
            fail('Google Maps отказа достъп. Проверете API ключа, разрешените адреси и настройките на проекта.');
            if (typeof previousAuthFailure === 'function') previousAuthFailure();
        };
        global.ipuzzleGoogleMapsReady = function () {
            if (state !== 'loading') return;
            global.clearTimeout(timeout);
            state = 'ready';
            clients.forEach(ready);
        };

        var url = new URL('https://maps.googleapis.com/maps/api/js');
        url.searchParams.set('callback', 'ipuzzleGoogleMapsReady');
        url.searchParams.set('loading', 'async');
        url.searchParams.set('v', 'quarterly');
        url.searchParams.set('libraries', 'marker');
        url.searchParams.set('language', 'bg');
        url.searchParams.set('region', 'BG');
        if (config.apiKey) url.searchParams.set('key', config.apiKey);
        var script = document.createElement('script');
        script.async = true;
        script.src = url.toString();
        script.onerror = function () { fail('Google Maps не може да бъде заредена. Проверете интернет връзката.'); };
        timeout = global.setTimeout(function () { fail('Google Maps не отговори навреме.'); }, 20000);
        document.head.appendChild(script);
    }

    function waitForTiles(map, onReady, onError) {
        var listener = null;
        var timer = global.setTimeout(function () {
            stop();
            onError('Google Maps не предостави изображение на картата. Проверете API ключа и връзката.');
        }, 15000);
        function stop() {
            global.clearTimeout(timer);
            if (listener) listener.remove();
        }
        listener = map.addListener('tilesloaded', function () {
            stop();
            onReady();
        });
        return stop;
    }

    global.IpuzzleGoogleMaps = { load: load, waitForTiles: waitForTiles };
}(window));
