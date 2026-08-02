{literal}
<script>
    rpc_debug = true;
    rpc_html_debug = true;

    var map = null;
    var marker = null;

    function showMapError(message) {
        var error = document.getElementById('map_error');
        var saveButton = document.getElementById('save_geo');

        if (error) {
            error.textContent = message;
            error.style.display = 'flex';
        }

        if (saveButton) {
            saveButton.disabled = true;
        }
    }

    function initialize(objectIdValue, latitudeValue, longitudeValue, zoomValue) {
        var objectId = parseInt(objectIdValue, 10);
        var latitude = parseFloat(latitudeValue);
        var longitude = parseFloat(longitudeValue);
        var zoom = parseInt(zoomValue, 10);

        if (!isFinite(objectId) || objectId <= 0) {
            showMapError('Няма привързан обект.');
            return;
        }

        if (typeof L === 'undefined') {
            showMapError('Картата не може да бъде заредена. Проверете интернет връзката и опитайте отново.');
            return;
        }

        if (!isFinite(latitude) || !isFinite(longitude)) {
            showMapError('Липсват валидни координати за центриране на картата.');
            return;
        }

        if (!isFinite(zoom)) {
            zoom = 14;
        }

        map = L.map('map_canvas', {
            doubleClickZoom: false,
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([latitude, longitude], zoom);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
        }).addTo(map);

        marker = L.marker([latitude, longitude], {
            draggable: true,
            title: 'Преместете маркера до точната позиция на обекта'
        }).addTo(map);

        window.setTimeout(function () {
            map.invalidateSize();
        }, 0);
    }

    function setGeoLatLan() {
        var position;

        if (!marker) {
            showMapError('Картата все още не е готова за запис.');
            return false;
        }

        position = marker.getLatLng();
        jQuery('#new_lan').val(position.lng);
        jQuery('#new_lat').val(position.lat);

        loadXMLDoc2('save');
        rpc_on_exit = function () {
            window.location.reload();
        };

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

    <div id="map_error" class="ui-object-map-error" role="alert" style="display:none;"></div>
    <div id="map_canvas" class="ui-object-map" style="width:100%; height:450px;"></div>

    <div class="fixed-bottom w-100 text-center ui-object-actions ui-object-geo-actions">
        <button type="button" id="save_geo" class="btn btn-sm btn-success px-5" onclick="return setGeoLatLan();">
            <span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запази
        </button>
    </div>
</form>

<script>
    initialize(
        {$nID|default:0},
        {$mapCenter.lat|default:42.7339},
        {$mapCenter.lng|default:25.4858},
        {$mapCenter.zoom|default:7}
    );
</script>
