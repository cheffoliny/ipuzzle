{literal}
	<script>
		rpc_debug=true;

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

		function initialize(povv) {
			var diva = $('map_canvas');

			{/literal}

			{if $nID <= 0 }
				alert("Няма привързан обект");
			{else}
                ppov        = povv;
				coor        = new google.maps.LatLng( {$aObject.geo_lat} ,{$aObject.geo_lan});
			{/if}

			{literal}

			map = new google.maps.Map(diva,{
				zoom: 14,
				maxZoom:17,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				disableDoubleClickZoom:true,
				panControl: true,
				zoomControl: true,
				draggable:true,
				disableDefaultUI:true,
				scrollwheel:true,
				center: coor,
                streetViewControl: false
            });

   			var marker = new google.maps.Marker({
		        position:   coor,
		        map:        map,
		        icon:       'http://maps.google.com/mapfiles/marker.png',
		        clickable:  true,
		        flat:		true,
		        draggable:	true,
		        visible : true,
		        zIndex:5000
		    });
 			var btnObjects = document.createElement("button");
			btnObjects.innerHTML = "Запиши координатите";

			btnObjects.onclick = function(){
				var pos = marker.getPosition();
				jQuery('#new_lan').val(pos.lng());
				jQuery('#new_lat').val(pos.lat());

				loadXMLDoc2('save');
			}

			var divObjects = document.createElement("div");
			divObjects.appendChild(btnObjects);
			divObjects.index = 1;

			map.controls[google.maps.ControlPosition.TOP_CENTER].push(divObjects);

            if ( !empty(ppov) && !empty(ppov.lat) ) {
                astorPlace  = new google.maps.LatLng(ppov.lat, ppov.lng);
            } else {
                astorPlace = coor;
            }

            panorama = map.getStreetView();
            panorama.setPosition(astorPlace);

            if ( !empty(ppov.heading) ) {
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
            google.maps.event.addDomListener(diva, 'click', function(event) {
                if ( clicktopoint ) {
                    var x = event.x - 32;
                    var y = event.y - 135;

                    var canvas = jQuery(diva);
                    var div = jQuery('<div/>', {name: 'marker'});
                    var img = jQuery('<img>').attr('src', 'images/marker1_red.png').appendTo(div);
                    div.css({top:y+'px',left:x+'px',position:'absolute',zIndex:999});
                    canvas.append(div);
                }
                //console.log(y);
            });

            google.maps.event.addListener(panorama, 'pov_changed', function() {
                var pov     = {};
                var poss    = panorama.getPosition();

                pov.heading = panorama.getPov().heading;
                pov.pitch   = panorama.getPov().pitch;
                pov.zoom    = panorama.getPov().zoom;
                pov.lng     = poss.lng();
                pov.lat     = poss.lat();

                var json    = JSON.stringify(pov);
                $('new_pov').value = json;
                $('savePov').disabled = false;
            });

            if ( typeof ppov.left == 'object' && toggle) {
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
                    div.css({top:y+'px',left:x+'px',position:'absolute',zIndex:999});
                    canvas.append(div);
                }
            }
		}

        function toggleStreetView() {
            toggle = panorama.getVisible();
            $('savePov').disabled = true;

            if (toggle == false) {
                disableMovement(false);
                panorama.setVisible(true);
                $('savePov').style.visibility = 'visible';
                $('clearPov').style.visibility = 'visible';
                $('saveCoords').style.visibility = 'visible';
                $('movePov').style.visibility = 'visible';
                var diva = $('map_canvas');

                if ( typeof ppov.left == 'object' ) {
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
                        div.css({top:y+'px',left:x+'px',position:'absolute',zIndex:999});
                        canvas.append(div);
                    }
                }
            } else {
                disableMovement(false);
                panorama.setVisible(false);
                $('savePov').style.visibility = 'hidden';
                $('clearPov').style.visibility = 'hidden';
                $('saveCoords').style.visibility = 'hidden';
                $('movePov').style.visibility = 'hidden';

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
                    //streetViewControl: false,

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
            if ( confirm('Наистина ли желаете да запазите изгледа?') ) {
                var arrLeft = new Array();
                var arrTop = new Array();

                $('ppov').value = $('new_pov').value;
                ppov = jQuery.parseJSON($('ppov').value);

                jQuery('div[name=marker]').each(function() {


                    var xx = parseInt(jQuery(this).css('left'), 10);
                    var yy = parseInt(jQuery(this).css('top'), 10);

                    arrLeft.push(xx);
                    arrTop.push(yy);

                    ppov.left = arrLeft;
                    ppov.top = arrTop;

                    var json = JSON.stringify(ppov);
                    $('new_pov').value = json;
                });

                loadXMLDoc2('saveLastPov');
                $('savePov').disabled = true;

                rpc_on_exit = function() {
                    clicktogo = false;
                    clicktopoint = false;

                    alert('Промените бяха запазени!');

                    var p = $('new_pov').value;
                    ppov = jQuery.parseJSON(p);

                    initialize(ppov);
                    disableMovement(false);
                    panorama.setVisible(true);
                }
            }
        }

        function clearLastPov() {

            if ( confirm('Наистина ли желаете да рестартирате изгледа?') ) {
                loadXMLDoc2('clearPov');

                rpc_on_exit = function() {
                    jQuery('div[name=marker]').remove();
                    var p = $('ppov').value;
                    ppov = jQuery.parseJSON(p);

                    initialize(ppov);
                    disableMovement(false);
                    panorama.setVisible(true);
                }
            }
        }

        function moveLastPov() {
            if ( confirm('Наистина ли желаете да редактирате изгледа? Всички точки ще бъдат изтрити!') ) {
                clicktogo = true;
                clicktopoint = true;

                jQuery('div[name=marker]').remove();
                var p = $('ppov').value;
                ppov = jQuery.parseJSON(p);
                $('savePov').disabled = false;

                initialize(ppov);
                //disableMovement(false);
                panorama.setVisible(true);
            }
        }

        //google.maps.event.addDomListener(window, 'load', initialize);
    </script>
{/literal}
<form action="" name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" name="nID" id="nID" value="{$nID|default:0}"/>
    <input type="hidden" name="ppov" id="ppov" value="{$pov|escape}">
    <input type="hidden" name="new_lan" id="new_lan" value="0" />
    <input type="hidden" name="new_lat" id="new_lat" value="0" />
    <input type="hidden" name="new_pov" id="new_pov" value="" />

<table class="search" style="width:100%;">
	<tr>
		<td class="header_buttons">
			<span id="head_window">Позиция на обект [{$aObject.num}] {$aObject.name}</span>
			<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
			{include file=object_tabs.tpl}
		</td>
	</tr>

	<tr class="odd">
		<td id="filter_result" style="padding: 1px 0 1px 0;">
			<div id="map_canvas" style="width:100%; height: 415px;" ></div>
		</td>
	</tr>
</table>

<div id="search"  style="width: 800px;">
	<table class="page_data" >
		<tr>
			<td style="text-align: left; padding: 10px 0 10px 2px;">
				<div class="input-group">
					<input type="radio" id="toMap" name="typeMap" value="toMap" checked="checked" onchange="toggleStreetView();" class="clear" /> Карта
            		<input type="radio" id="toStreet" name="typeMap" value="toStreet" onchange="toggleStreetView();" class="clear" /> Снимка
				</div>
			</td>

			<td style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
				<button class="btn btn-xs btn-info" 	type="button" id="savePov" name="savePov" onclick="saveLastPov(); return false;" disabled="disabled" style="visibility: hidden;"><img src="images/glyphicons/save.png" style="width: 14px; height: 14px;"> Запази </button>
	            <button class="btn btn-xs btn-danger" 	type="button" id="clearPov" name="clearPov" onclick="clearLastPov(); return false;" style="visibility: hidden;"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Изчисти </button>
	            <button class="btn btn-xs btn-success" 	type="button" id="movePov" name="movePov" onclick="moveLastPov(); return false;" style="visibility: hidden;"><i class="fa fa-edit"></i> Редакция</button>
	            <input type="checkbox" id="saveCoords" name="saveCoords" style="visibility: hidden;" class="clear" /> Запазване на позиция
			</td>
		</tr>
	</table>
</div>
</form>

	<script>
		initialize({$pov});
	</script>
