{literal}
<script>
    rpc_debug = true;
    rpc_html_debug = true;

    var map = null;
    var marker = null;
    var mapProvider = null;

    function markerPosition() {
        if (!marker) return null;
        if (mapProvider === 'leaflet') return marker.getLatLng();
        var position = marker.position;
        return position ? {
            lat: typeof position.lat === 'function' ? position.lat() : position.lat,
            lng: typeof position.lng === 'function' ? position.lng() : position.lng
        } : null;
    }

    function updateGoogleMapsLink(position) {
        var url = new URL('https://www.google.com/maps/search/');
        url.searchParams.set('api', '1');
        url.searchParams.set('query', position.lat + ',' + position.lng);
        document.getElementById('open_google_maps').href = url.toString();
    }

    function showMapError(message) {
        var error = document.getElementById('map_error');
        var saveButton = document.getElementById('save_geo');

        if (error) {
            error.textContent = message;
            error.classList.remove('d-none');
        }

        if (saveButton) {
            saveButton.disabled = true;
        }
    }

    function initialize(objectIdValue, latitudeValue, longitudeValue, zoomValue, config) {
        var objectId = parseInt(objectIdValue, 10);
        var latitude = parseFloat(latitudeValue);
        var longitude = parseFloat(longitudeValue);
        var zoom = parseInt(zoomValue, 10);

        if (!isFinite(objectId) || objectId <= 0) {
            showMapError('Няма привързан обект.');
            return;
        }

        if (!isFinite(latitude) || !isFinite(longitude) || Math.abs(latitude) > 90 || Math.abs(longitude) > 180) {
            showMapError('Липсват валидни координати за центриране на картата.');
            return;
        }

        if (!isFinite(zoom)) {
            zoom = 14;
        }

        var center = { lat: latitude, lng: longitude };
        var stopWaitingForTiles = function () {};
        updateGoogleMapsLink(center);

        function useFallback(message) {
            if (mapProvider === 'leaflet') return;
            stopWaitingForTiles();
            var position = markerPosition() || center;
            if (marker && mapProvider === 'google') marker.map = null;
            if (map && mapProvider === 'google') {
                map.getStreetView().setVisible(false);
                google.maps.event.clearInstanceListeners(map);
            }
            marker = null;
            map = null;
            mapProvider = 'leaflet';
            showMapError(message);
            if (typeof L === 'undefined') return;

            // Isolate the fallback from late updates of the failed Google map.
            var oldCanvas = document.getElementById('map_canvas');
            var canvas = oldCanvas.cloneNode(false);
            oldCanvas.parentNode.replaceChild(canvas, oldCanvas);
            map = L.map(canvas, { doubleClickZoom: false }).setView([position.lat, position.lng], zoom);
            var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
            });
            var fallbackTimeout = window.setTimeout(function () {
                showMapError(message + ' Резервната карта също не може да бъде заредена.');
            }, 15000);
            tiles.once('tileload', function () {
                window.clearTimeout(fallbackTimeout);
                document.getElementById('map_error').textContent = message + ' Показана е резервна карта OpenStreetMap.';
                document.getElementById('save_geo').disabled = false;
            });
            tiles.addTo(map);
            marker = L.marker([position.lat, position.lng], {
                draggable: true,
                title: 'Преместете маркера до точната позиция на обекта'
            }).addTo(map);
            marker.on('dragend', function () { updateGoogleMapsLink(markerPosition()); });
            document.getElementById('map_error').textContent = message + ' Показана е резервна карта OpenStreetMap.';
            window.setTimeout(function () { map.invalidateSize(); }, 0);
        }

        IpuzzleGoogleMaps.load(config, function (maps) {
            if (mapProvider === 'leaflet') return;
            mapProvider = 'google';
            map = new maps.Map(document.getElementById('map_canvas'), {
                center: center,
                zoom: zoom,
                mapId: config.mapId || 'DEMO_MAP_ID',
                mapTypeId: maps.MapTypeId.HYBRID,
                disableDoubleClickZoom: true,
                streetViewControl: true,
                mapTypeControl: true,
                fullscreenControl: true
            });
            marker = new maps.marker.AdvancedMarkerElement({
                map: map,
                position: center,
                gmpDraggable: true,
                title: 'Преместете маркера до точната позиция на обекта'
            });
            marker.addListener('dragend', function () { updateGoogleMapsLink(markerPosition()); });
            map.addListener('click', function (event) {
                if (!event.latLng) return;
                marker.position = event.latLng;
                updateGoogleMapsLink(markerPosition());
            });
            stopWaitingForTiles = IpuzzleGoogleMaps.waitForTiles(map, function () {
                document.getElementById('save_geo').disabled = false;
            }, useFallback);
        }, useFallback);
    }

    function setGeoLatLan() {
        var position;

        if (document.getElementById('save_geo').disabled) return false;

        if (!marker) {
            showMapError('Картата все още не е готова за запис.');
            return false;
        }

        position = markerPosition();
        if (!position || !isFinite(position.lat) || !isFinite(position.lng)) return false;
        jQuery('#new_lan').val(position.lng);
        jQuery('#new_lat').val(position.lat);

        if (_rpc_blocked) return false;
        var previousExit = rpc_on_exit;
        rpc_on_exit = function (error) {
            rpc_on_exit = previousExit;
            if (error === 0) window.location.reload();
        };
        loadXMLDoc2('save');

        return false;
    }
</script>
{/literal}

<form name="form1" id="form1" class="ui-object-core ui-object-geo" onsubmit="return false;">
    <input type="hidden" name="nID" id="nID" value="{$nID|default:0}" />
    <input type="hidden" name="ppov" id="ppov" value="{$pov|escape}" />
    <input type="hidden" name="new_lan" id="new_lan" value="0" />
    <input type="hidden" name="new_lat" id="new_lat" value="0" />
    <input type="hidden" name="new_pov" id="new_pov" value="" />

    {include file="object_tabs.tpl"}

    <div id="map_error" class="ui-object-map-error d-none" role="alert"></div>
    <div id="map_canvas" class="ui-object-map"></div>

    <div class="fixed-bottom w-100 text-center ui-object-actions ui-object-geo-actions">
        <a id="open_google_maps" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer" title="Отвори позицията в Google Maps">
            <span class="ui-icon ui-icon-external-link" aria-hidden="true"></span> Google Maps
        </a>
        <button type="button" id="save_geo" class="btn btn-sm btn-success px-5" disabled onclick="return setGeoLatLan();">
            <span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запази
        </button>
    </div>
</form>

<script>
    initialize(
        {$nID|default:0},
        {$mapCenter.lat|default:42.7339},
        {$mapCenter.lng|default:25.4858},
        {$mapCenter.zoom|default:7},
        {$googleMapsConfigJson}
    );
</script>
