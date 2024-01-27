{literal}
<script>
    rpc_debug = true;

    var map;
    var panorama;
    var astorPlace;
    var drag = false;
    var ppov;
    var coor;
    var clicktogo = false;
    var clicktopoint = false;
    var dragablecontrol = false;
    var toggle;
    var autocomplete;
    var countryRestrict = {'country': 'bg'};
    var marker;

    var onPlaceChanged = function () {

//        console.log('address');
//
        var place = autocomplete.getPlace();
        if (place.geometry) {
            marker.setPosition(place.geometry.location);
            map.panTo(place.geometry.location);
        } else {
//                document.getElementById('locationAddress').placeholder = 'Изберете адрес!';
            document.getElementById('pac-input').placeholder = 'Изберете адрес!';
        }


    }

    jQuery(document).ready(function () {
        jQuery('input[type="text"]').keypress(function (e) {
            var code = e.keyCode || e.which;
            if (code === 13)
                e.preventDefault();
        });
    });


    jQuery('#form1').on('submit', function () {
        event.preventDefault();
        return false;
    });

    function initialize(povv) {
        var diva = $('map_canvas');

        {/literal}

        {if $nID <= 0 }
        alert("Няма привързан обект");
        {else}
        ppov = povv;

        {if $aObject.geo_lat != 0 }
        coor = new google.maps.LatLng({$aObject.geo_lat}, {$aObject.geo_lan});
        {elseif $aCities.geo_lat != 0}
        coor = new google.maps.LatLng({$aCities.geo_lat}, {$aCities.geo_lan});
        {elseif $aOffices.geo_lat != 0}
        coor = new google.maps.LatLng({$aOffices.geo_lat}, {$aOffices.geo_lan});
        {else}
        coor = new google.maps.LatLng({$aObject.geo_lat}, {$aObject.geo_lan});
        {/if}

        {/if}

        {literal}

        map = new google.maps.Map(diva, {
            zoom: 14,
//            maxZoom: 17,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDoubleClickZoom: true,
            panControl: true,
            zoomControl: true,
            draggable: true,
            disableDefaultUI: true,
            scrollwheel: true,
            center: coor,
            streetViewControl: false
        });

        if ($('savePov').disabled) {
            autocomplete = new google.maps.places.Autocomplete(
//                     (  document.getElementById('locationAddress')), {
                (  document.getElementById('pac-input')), {
                    types: ['address'],
                    componentRestrictions: countryRestrict
                });
            autocomplete.bindTo('bounds', map);
            autocomplete.addListener('place_changed', onPlaceChanged);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('pac-input'));
        }

        marker = new google.maps.Marker({
            position: coor,
            map: map,
            icon: 'https://maps.google.com/mapfiles/marker.png',
            clickable: true,
            flat: true,
            draggable: true,
            visible: true,
            zIndex: 5000
        });
        var btnObjects = document.createElement("button");
        btnObjects.setAttribute('class', 'controls btn btn-info');
        btnObjects.innerHTML = "Запиши";

        jQuery(btnObjects).on('click', function () {
            var pos = marker.getPosition();
            jQuery('#new_lan').val(pos.lng());
            jQuery('#new_lat').val(pos.lat());

            loadXMLDoc2('save');
            rpc_on_exit = function() {
                window.location.reload();
            }
        });

        var divObjects = document.createElement("div");
        divObjects.appendChild(btnObjects);
        divObjects.index = 1;

        map.controls[google.maps.ControlPosition.TOP_CENTER].push(divObjects);

        if (!empty(ppov) && !empty(ppov.lat)) {
            astorPlace = new google.maps.LatLng(ppov.lat, ppov.lng);
        } else {
            astorPlace = coor;
        }

        panorama = map.getStreetView();
        panorama.setPosition(astorPlace);

        if (!empty(ppov.heading)) {
            //astorPlace  = new google.maps.LatLng(ppov.lat, ppov.lng);

            panorama.setPov(({
                heading: ppov.heading, //59.249168,
                pitch: ppov.pitch, //19,
                zoom: ppov.zoom     // 2.5
            }));
        } else {
            astorPlace = coor;
        }

        //google.maps.event.addDomListener(diva, 'click', showAlert);
        google.maps.event.addDomListener(diva, 'click', function (event) {
            if (clicktopoint) {
                var x = event.x - 32;
                var y = event.y - 135;

                var canvas = jQuery(diva);
                var div = jQuery('<div/>', {name: 'marker'});
                var img = jQuery('<img>').attr('src', 'images/marker1_red.png').appendTo(div);
                div.css({top: y + 'px', left: x + 'px', position: 'absolute', zIndex: 999});
                canvas.append(div);
            }
            //console.log(y);
        });

        google.maps.event.addListener(panorama, 'pov_changed', function () {
            var pov = {};
            var poss = panorama.getPosition();

            pov.heading = panorama.getPov().heading;
            pov.pitch = panorama.getPov().pitch;
            pov.zoom = panorama.getPov().zoom;
            pov.lng = poss.lng();
            pov.lat = poss.lat();

            var json = JSON.stringify(pov);
            $('new_pov').value = json;
            $('savePov').disabled = false;
        });

        if (typeof ppov.left == 'object' && toggle) {
            jQuery('div[name=marker]').remove();

            var aX = ppov.left;
            var aY = ppov.top;
            var length = aX.length;

            for (var i = 0; i < length; i++) {
                var x = aX[i];
                var y = aY[i];

                var canvas = jQuery(diva);
                var div = jQuery('<div/>', {name: 'marker'});
                var img = jQuery('<img>').attr('src', 'images/marker1_red.png').appendTo(div);
                div.css({top: y + 'px', left: x + 'px', position: 'absolute', zIndex: 999});
                canvas.append(div);
            }
        }
    }

    function toggleStreetView(toggleParam) {

        toggle = (typeof(toggleParam) != 'undefined')? toggleParam : panorama.getVisible();
        $('savePov').disabled = true;

        if (toggle == false) {
            disableMovement(false);
            panorama.setVisible(true);
            $('savePov').style.visibility = 'visible';
            $('clearPov').style.visibility = 'visible';
//            $('saveCoords').style.visibility = 'visible';
//            jQuery('.save-pos').show();
//            $('movePov').style.visibility = 'visible';
            var diva = $('map_canvas');

            if (typeof ppov.left == 'object') {
                jQuery('div[name=marker]').remove();

                var aX = ppov.left;
                var aY = ppov.top;
                var length = aX.length;

                for (var i = 0; i < length; i++) {
                    var x = aX[i];
                    var y = aY[i];

                    var canvas = jQuery(diva);
                    var div = jQuery('<div/>', {name: 'marker'});
                    var img = jQuery('<img>').attr('src', 'images/marker1_red.png').appendTo(div);
                    div.css({top: y + 'px', left: x + 'px', position: 'absolute', zIndex: 999});
                    canvas.append(div);
                }
            }
        } else {
            disableMovement(false);
            panorama.setVisible(false);
            $('savePov').style.visibility = 'hidden';
            $('clearPov').style.visibility = 'hidden';
            $('saveCoords').style.visibility = 'hidden';
            jQuery('.save-pos').hide();
//            $('movePov').style.visibility = 'hidden';

            jQuery('div[name=marker]').remove();
        }
    }

    function disableMovement(dragable) {
        var mapOptions;

        if (!dragable) {
            mapOptions = {
                //draggable: false,
                //scrollwheel: false,
                //disableDoubleClickZoom: true,
                //zoomControl: false,
                //disableDefaultUI: true,
                //streetViewControl: false

                addressControl: false,
                enableCloseButton: false,
                navigationControl: false,
                draggable: false,
                panControl: dragablecontrol,
                disableDefaultUI: true,
                zoomControl: dragablecontrol,
                rotateControl: false,
                linksControl: false,
                clickToGo: clicktogo,
                imageDateControl: false,
                disableDoubleClickZoom: true
            };
        } else {
            mapOptions = {
                draggable: true,
                scrollwheel: true,
                disableDoubleClickZoom: true,
                zoomControl: true,
                disableDefaultUI: true
            };
        }

        panorama.setOptions(mapOptions);
    }

    function saveLastPov() {
        if (confirm('Наистина ли желаете да запазите изгледа?')) {
            var arrLeft = new Array();
            var arrTop = new Array();

            if(jQuery('#new_pov').val() != "") {
                jQuery('#ppov').val(jQuery('#new_pov').val());
            }
            var ppov = jQuery.parseJSON(jQuery('#ppov').val());

            jQuery('div[name=marker]').each(function () {


                var yy = parseInt(jQuery(this).css('top'), 10);
                var xx = parseInt(jQuery(this).css('left'), 10);
                arrTop.push(yy);
                arrLeft.push(xx);

                ppov.left = arrLeft;
                ppov.top = arrTop;

                var json = JSON.stringify(ppov);
                $('new_pov').value = json;
            });

            loadXMLDoc2('saveLastPov');
            $('savePov').disabled = true;

            rpc_on_exit = function () {
                clicktogo = false;
                clicktopoint = false;

                alert('Промените бяха запазени!');
                window.location.reload();

//                var p = $('new_pov').value;
//                ppov = jQuery.parseJSON(p);
//
//                initialize(ppov);
//                disableMovement(false);
//                panorama.setVisible(true);
            }

            jQuery('#points').hide();
//            jQuery('#movePov').show();
        }
    }

    function clearLastPov() {

        if (confirm('Наистина ли желаете да рестартирате изгледа?')) {
            loadXMLDoc2('clearPov');

            rpc_on_exit = function () {
                jQuery('div[name=marker]').remove();
                var p = $('ppov').value;
                ppov = jQuery.parseJSON(p);

                initialize(ppov);
                disableMovement(false);
                panorama.setVisible(true);
            }

            jQuery('#points').hide();
//            jQuery('#movePov').show();
        }
    }

    function moveLastPov() {
        if (confirm('Наистина ли желаете да редактирате изгледа? Всички точки ще бъдат изтрити!')) {
            clicktogo = true;
            clicktopoint = false;

//            jQuery('#movePov').hide();
//            jQuery('#points').show();

            jQuery('div[name=marker]').remove();
            var p = $('ppov').value;
            ppov = jQuery.parseJSON(p);
            $('savePov').disabled = false;

            initialize(ppov);
            //disableMovement(false);
            panorama.setVisible(true);
        }
    }

    function clearAsPoints() {
        if (confirm('Желаете ли всички точки да бъдат изтрити?')) {
            jQuery('div[name=marker]').remove();
        }
    }

    function addAsPoints() {
        clicktogo = false;
        clicktopoint = true;
        disableMovement(false);

        var p = $('ppov').value;
        ppov = jQuery.parseJSON(p);

        //initialize(ppov);
        panorama.setVisible(true);
    }


    function addressToLocation(address, callback) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode(
            {
                address: address
            },
            function (results, status) {

                var resultLocations = [];

                if (status == google.maps.GeocoderStatus.OK) {
                    if (results) {
                        var numOfResults = results.length;
                        for (var i = 0; i < numOfResults; i++) {
                            var result = results[i];
                            resultLocations.push(
                                {
                                    text: result.formatted_address,
                                    addressStr: result.formatted_address,
                                    location: result.geometry.location
                                }
                            );
                        }
                        ;
                    }
                } else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
                    // address not found
                }

                if (resultLocations.length > 0) {
                    callback(resultLocations);
                } else {
                    callback(null);
                }
            }
        );
    }

    function switchTab(e) {

        var jEl = jQuery(e);
        jQuery('.dropdown').find('.active').removeClass('active');
        jEl.addClass('active');
        jQuery('#dropdown_text').html(jEl.find('a').html());

        var sAction = jEl.attr('id');

        switch(sAction) {
            case "map_view" :
                toggleStreetView(true);
                jQuery('#points').hide();
                break;
            case "street_view" :
                toggleStreetView(false);
                jQuery('#points').hide();
                $('savePov').style.visibility = 'hidden';
                $('clearPov').style.visibility = 'hidden';
                break;
            case "edit":
                moveLastPov();
                jQuery('#points').hide();
                $('savePov').style.visibility = 'visible';
                $('clearPov').style.visibility = 'visible';
                $('clearPov').style.display = 'inline';
                break;

            case 'pointsEdit':
                toggleStreetView(false);
                jQuery('#points').show();
                $('savePov').style.visibility = 'visible';
                jQuery('#savePov').removeAttr('disabled');
                $('clearPov').style.display = 'none';
//                jQuery('#clearPov').removeAttr('disabled');
                break;


        }
//        console.log(sAction);
    }

    //google.maps.event.addDomListener(window, 'load', initialize);
</script>

    <style type="text/css">
        #map_canvas {
            height: 100%;
        }



    </style>
{/literal}
<form action="" name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" name="nID" id="nID" value="{$nID|default:0}"/>
    <input type="hidden" name="ppov" id="ppov" value="{$pov|escape}">
    <input type="hidden" name="new_lan" id="new_lan" value="0"/>
    <input type="hidden" name="new_lat" id="new_lat" value="0"/>
    <input type="hidden" name="new_pov" id="new_pov" value=""/>

    {include file='object_tabs.tpl'}

    <div id="filter" class="container-fluid mb-4 mx-2">
        <div class="row h-100 mt-2">
            <div id="filter_result" class="col px-1">
                <div id="map_canvas" class="w-100" ></div>
            </div>
        </div>
    </div>
</form>

<script>
    initialize({$pov});
</script>
