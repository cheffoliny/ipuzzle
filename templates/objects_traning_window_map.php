<?php
require_once ("../config/function.autoload.php");
require_once ("../config/header.inc.php");	
require_once ("../config/session.inc.php");
require_once ("../config/connect.inc.php");
require_once ("../include/general.inc.php");
global $db_personnel, $db_name_personnel, $db_sod, $db_name_sod;
	// google maps
	$aGoogleKey['telepol.net']				= "ABQIAAAAvogtsTYeCCn9bb52RPbh-xRv7QWxkr-3IKJEXfTogZixrXvNrxTFUqzQfNBIIEf8T4kg00Wf1-SGFw";
	$aGoogleKey['telenet1.telepol.com']		= "ABQIAAAAvogtsTYeCCn9bb52RPbh-xQgXI407SWkrCxD7M_O9RWeWzWn6RSNTfQvxkRbeE7SAJbtK4I5-WfXOQ";
	$aGoogleKey['telenet2.telepol.com']		= "ABQIAAAAdouWIVeqAFAeslBKto6N4BShAnn75XYqscKnPf6_hzGF3TrSxhRTvQJKhEDsGJpITp3aF0FMhCQF5g";
	$aGoogleKey['telenet141.telepol.com']	= "ABQIAAAA_HX4nq6cEtwYxoZOoHwsoRT6DNrntbAvLOhrdQYN5rbYhqEAShRhu9ZGvGvbQNA9vYpzD-5qsLBfpw";
	$aGoogleKey['telenet3.telepol.com']		= "ABQIAAAA_HX4nq6cEtwYxoZOoHwsoRQMDjmGTT5ypxXB9Ff318Fyipe0JBRKL5KUgOugQrAGnSgdvz5_aP_VbA";
	$aGoogleKey['telenet4.telepol.com']		= "ABQIAAAA_HX4nq6cEtwYxoZOoHwsoRTCp9nmh9mFF2L6s8hmh4hPFidbkhTHS1ll0RuDXDEuGdIXVWIQKYi0jA";

	$aGoogleKey['test']						= "ABQIAAAAdouWIVeqAFAeslBKto6N4BRbZHnKkPiOgFOiUuTNWhFQ49yrExRhejihQkQuG_BNLFEVRHwgqxuYfw";

	// Ключ, за да работи 213.91.252.171/telenet
	$aGoogleKey['me']						= "ABQIAAAA_HX4nq6cEtwYxoZOoHwsoRSfR_24ogle1PQ1-HK0Qt4m44VuEhR7KHTJGU7QlOCW3VrY-37xPd4uJQ";
	$aGoogleKey['misho_ruse']				= "ABQIAAAA_HX4nq6cEtwYxoZOoHwsoRQXbLfHs64ZxiPj7gaGyb9LYpEnFxTLl7idOPXVifL2BLSsj2GRoz4A4Q";
		
	
	switch( $_SERVER["HTTP_HOST"] ) {
		case '213.91.252.171' : 
			$GoogleKey = $aGoogleKey['me'];
			break;
		case '172.16.1.122:81' : 
			$GoogleKey = $aGoogleKey['misho_ruse'];
			break;
		case '213.91.252.135' : 
			$GoogleKey = $aGoogleKey['telenet1.telepol.com'];
			break;
		case '213.91.252.162' : 
			$GoogleKey = $aGoogleKey['telenet2.telepol.com'];
			break;
		case '213.91.252.141' : 
			$GoogleKey = $aGoogleKey['telenet141.telepol.com'];
			break;
		case '213.91.252.129' :
			$GoogleKey = $aGoogleKey['telenet4.telepol.com'];
			break;			
		case '213.91.252.198' :
			$GoogleKey = $aGoogleKey['telenet3.telepol.com'];
			break;			
		default:
			$GoogleKey = $aGoogleKey['telepol.net'];
			break;
	}

//	$nLanMin		= !isset($_GET['nLanMin']) ? 0 : $_GET['nLanMin'];
//	$nLanMax		= !isset($_GET['nLanMax']) ? 0 : $_GET['nLanMax'];
//	$nLatMin		= !isset($_GET['nLatMin']) ? 0 : $_GET['nLatMin'];
//	$nLatMax 		= !isset($_GET['nLatMax']) ? 0 : $_GET['nLatMax'];
	$nIDPerson		= !isset($_GET['nIDPerson']) ? 0 : $_GET['nIDPerson'];
	$nIDOffice		= !isset($_GET['nIDOffice']) ? 0 : $_GET['nIDOffice'];
	$tType			= !isset($_GET['tType']) ? 0 : $_GET['tType'];
	$nDateFrom		= !isset($_GET['dateFrom']) ? 0 : $_GET['dateFrom'];
	$nDateTo		= !isset($_GET['dateTo']) ? 0 : $_GET['dateTo'];
	
	$oBase = new DBBase2($db_personnel,'personnel');

	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS				
			(SELECT GROUP_CONCAT(id) 
			 FROM {$db_name_sod}.objects 
			 WHERE id_office=$nIDOffice 
			 AND id_status=1 
			 AND id NOT IN(IF(GROUP_CONCAT(DISTINCT vok.id_object),GROUP_CONCAT(DISTINCT vok.id_object),0))) AS unknown_objects,
			GROUP_CONCAT(DISTINCT vok.id_object)	AS known_objects,
			GROUP_CONCAT(DISTINCT vis.id_object)	AS visited_objects,
			GROUP_CONCAT(DISTINCT vor.id_object)	AS reacted_objects,
			o.geo_lat								AS office_geo_lat,
			o.geo_lan								AS office_geo_lan
		FROM $db_name_personnel.personnel	AS p
		LEFT JOIN $db_name_sod.offices AS o ON o.id=p.id_office
		LEFT JOIN $db_name_sod.visited_objects AS vok ON vok.id_person = p.id AND vok.type='familiar' AND vok.created_time >= '$nDateFrom' AND vok.created_time <= '$nDateTo' 
		LEFT JOIN $db_name_sod.visited_objects AS vis ON vis.id_person = p.id AND vis.type='visited'  AND vis.created_time >= '$nDateFrom' AND vis.created_time <= '$nDateTo' 
		LEFT JOIN $db_name_sod.visited_objects AS vor ON vor.id_person = p.id AND vor.type='reacted'  AND vor.created_time >= '$nDateFrom' AND vor.created_time <= '$nDateTo'

		WHERE 1
		AND p.id = $nIDPerson
		AND p.id_office = $nIDOffice

		GROUP BY p.id
	";	
	$aObjects = $oBase->select($sQuery);
        //RAZMENENI SA NA REALNATA
	$nCenterLat = $aObjects[0]['office_geo_lan'];
	$nCenterLan = $aObjects[0]['office_geo_lat'];	
	switch ($_GET['tType']) {
		case "known":		
			$sObjects = $aObjects[0]['known_objects'];
		break;
		case "unknown":
			$sObjects = $aObjects[0]['unknown_objects'];
		break;
		case "reacted":		
			$sObjects = $aObjects[0]['reacted_objects'];
		break;
		case "visited":
			$sObjects = $aObjects[0]['visited_objects'];
		break;
	}
	if (!empty($sObjects)) {
		$oBase = new DBBase2($db_sod,'objects');
		$sQuery = "SELECT id,CONCAT(num,'::',name,'::',geo_lat,'::',geo_lan,'::',id) AS object_data FROM $db_name_sod.objects WHERE id IN($sObjects)";
		$aObjects = $oBase->selectAssoc($sQuery);
		$sObjects = implode("@",$aObjects);			
	}
?>
<script>
	var bgMaps;
	var markers=[];
	var bShowAllObjects = false;
	var gMaps			= function() {      
		var nCenterLat	= document.getElementById("nCenterLat").value;
		var nCenterLan	= document.getElementById("nCenterLan").value;
        var latlng  = new google.maps.LatLng(nCenterLan,nCenterLat);
        var myOptions = {
			zoom: 12,
			zoomControl: true,
			panControl: false,
			streetViewControl: false,
			mapTypeControl: false,
			draggable : true,
			center: latlng,			
			mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        bgMap = new google.maps.Map(document.getElementById('map_canvas'),myOptions);                                            
        //google.maps.event.trigger(bgMap,'resize');     
		google.maps.event.addListener(bgMap, 'idle', loadObjects);
	}
	var init = function() {
		var script = document.createElement("script");
		script.type = "text/javascript";
		//script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=gMaps&key=<?=$GoogleKey; ?>";
		document.body.appendChild(script);		
	}
	
	var showSelectedObjects = function(e) {		
		var sel = e.target.parentNode;						
		for (var i=0; i<sel.options.length; i++) {			
			if (markers[sel.options[i].value] && sel.options[i].selected) {
				markers[sel.options[i].value].setVisible(true);
			} else if(markers[sel.options[i].value] && !sel.options[i].selected) {
				markers[sel.options[i].value].setVisible(false);
			} else if(!markers[sel.options[i].value]) {
				//alert("Не са въведени гео-координати за обекта!");
			}

		}					
	} 
	
	var loadObjects = function() {
		var _sObjects = document.getElementById('sObjects').value;		
		if (!_sObjects) { google.maps.event.clearListeners(bgMap,'idle'); return;	}
		var aObjects = _sObjects.split("@");				
		var listObjects = document.createElement("select");
			listObjects.style.width = "100%";
			listObjects.style.heigth = "100%";			
			listObjects.style.display = "none";
			listObjects.setAttribute('multiple', '');
			listObjects.setAttribute('size', '10');
		
		var btnObjects = document.createElement("button");
			btnObjects.innerHTML = "Покажи списък";
			btnObjects.style.float='left';
			btnObjects.style.width="167px"			
			btnObjects.onclick = function(){ 
				if (listObjects.style.display=='none') {
					listObjects.style.display=''
					btnObjects.innerHTML = "Скрий списък";
				} else {
					listObjects.style.display='none'
					btnObjects.innerHTML = "Покажи списък";
				}
			}
			
		var btnObjectsAll = document.createElement("button");
			btnObjectsAll.style.width="30px"
			btnObjectsAll.style.float='left';
			btnObjectsAll.style.borderRadius='5px';
			btnObjectsAll.style.background ="#cccccc url(../canvas/img/eye-close.png) no-repeat center";
			btnObjectsAll.setAttribute('title', 'Покажи/Скрий всички обекти');
			btnObjectsAll.onclick = function() {				
				if (bShowAllObjects) {
					btnObjectsAll.style.background ="#cccccc url(../canvas/img/eye-close.png) no-repeat center";					
					bShowAllObjects=false
				} else {
					btnObjectsAll.style.background ="#cccccc url(../canvas/img/eye.png) no-repeat center";					
					bShowAllObjects=true;
				}
				for (var i in markers) {
					if (markers.hasOwnProperty(i)) markers[i].setVisible(bShowAllObjects);					
				}
				for(i=0;i<listObjects.options.length;i++) {
					listObjects.options[i].selected = bShowAllObjects;											
				}
			}
				
					
		for (var i in aObjects) {		
			var geo_lat = aObjects[i].split('::')[2];
			var geo_lan = aObjects[i].split('::')[3];
			
			var title   = "[" +aObjects[i].split('::')[0]+"] "+aObjects[i].split('::')[1];
			var id		= parseInt(aObjects[i].split('::')[4]);
			if (geo_lat!=0 && geo_lan!=0) {
				markers[id] = new google.maps.Marker({
										position: new google.maps.LatLng(geo_lat,geo_lan), 							
										draggable:false,
										title:title,
										map:bgMap,
										id:id										
									});	
				markers[id].setVisible(false);
			}
			
			var optObjects = document.createElement("option");
				optObjects.text = title;
				optObjects.value = id;
				if (geo_lat==0 && geo_lan==0) {
					optObjects.style.color = "red";
					optObjects.title = "Без координати"
				}
			listObjects.add(optObjects);
			listObjects.onclick = showSelectedObjects;			
		}
		
		
		var divObjects = document.createElement("div");
			divObjects.style.width="200px";
			divObjects.appendChild(btnObjects);
			divObjects.appendChild(btnObjectsAll);
			divObjects.appendChild(listObjects);
			divObjects.index = 1;
		bgMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(divObjects);  
		google.maps.event.clearListeners(bgMap,'idle');
		
	}
</script>
<html>	 
	<body   style="margin:0; padding:0;" onload="init();"> 
		<form id="mapData" name="mapdata" onsubmit="return( false );" >
			<input type="hidden" value="<?=$nLanMin; ?>" 	id="nLanMin"  name="nLanMin"/>
			<input type="hidden" value="<?=$nLanMax; ?>" 	id="nLanMax"   name="nLanMax"/>
			<input type="hidden" value="<?=$nLatMin; ?>"	id="nLatMin"   name="nLatMin"/>
			<input type="hidden" value="<?=$nLatMax; ?>" 	id="nLatMax"   name="nLatMax"/>
			<input type="hidden" value="<?=$nCenterLat; ?>" id="nCenterLat"  name="nCenterLat"/>
			<input type="hidden" value="<?=$nCenterLan; ?>" id="nCenterLan"  name="nCenterLan" />
			<input type="hidden" value="<?=$GoogleKey; ?>" 	id="GoogleKey"  name="GoogleKey" />
			<input type="hidden" value="<?=$sObjects; ?>"	id="sObjects"  name="sObjects" />			
			<input type="hidden" id="lat" name="lat" />
			<input type="hidden" id="lon" name="lon" />		
		 </form>		
		<div id="map_canvas" style="width:100%; height: 650px;" ></div>	
	</body>
</html>