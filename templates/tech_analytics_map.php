<?php
	$nLanMin	= !isset($_GET['nLanMin']) ? 0 : $_GET['nLanMin'];
	$nLanMax	= !isset($_GET['nLanMax']) ? 0 : $_GET['nLanMax'];
	$nLatMin	= !isset($_GET['nLatMin']) ? 0 : $_GET['nLatMin'];
	$nLatMax 	= !isset($_GET['nLatMax']) ? 0 : $_GET['nLatMax'];
	$nCenterLat 	= !isset($_GET['nCenterLat']) ? 0 : $_GET['nCenterLat'];
	$nCenterLan 	= !isset($_GET['nCenterLan']) ? 0 : $_GET['nCenterLan'];
	$GoogleKey 	= !isset($_GET['GoogleKey']) ? 0 : $_GET['GoogleKey'];
	$sKmzFile 	= !isset($_GET['sKmzFile']) ? 0 : $_GET['sKmzFile'];
?>

<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=<?=$GoogleKey; ?>&amp;" type="text/javascript"></script>
	 
 <script type="text/javascript" >
	 
	 	var map;
		var ObjectMarker;
		var oCenter;
		var nLevel;
		var Objects;
	 	var markerOptions;
	 	
	 function initialize() 
	 {
		var sKmzFile = document.getElementById( 'sKmzFile' ).value;
	 	
	 	var nLanMin 	= document.getElementById("nLanMin").value;
		var nLanMax 	= document.getElementById("nLanMax").value;
		var nLatMin 	= document.getElementById("nLatMin").value;
		var nLatMax 	= document.getElementById("nLatMax").value;
		
		var nCenterLat	= document.getElementById("nCenterLat").value;
		var nCenterLan	= document.getElementById("nCenterLan").value;
		
		oCenter = new GLatLng(nCenterLat, nCenterLan);
		
		
	      if (GBrowserIsCompatible()) 
	      {
	        	map = new GMap2(document.getElementById("map_canvas"));
 			
	       	map.setUIToDefault();
 			
		var sw 		= new GLatLng(nLatMin, nLanMin); 
	           var ne		= new GLatLng(nLatMax, nLanMax); 
	        	var bounds 	= new GLatLngBounds(sw, ne); 
	        	nLevel  	= map.getBoundsZoomLevel(bounds); 

	   	 map.setCenter(new GLatLng(nCenterLat, nCenterLan), nLevel );
 		 map.addMapType(G_SATELLITE_3D_MAP);
	   	    
		 
			//object marker
			ObjectMarker = new GMarker(oCenter, {draggable: true});
     			
			GEvent.addListener(ObjectMarker, "drag", function() {
			          var latlng = ObjectMarker.getLatLng();
			          parent.document.getElementById('lat').value = document.getElementById('lat').value =  latlng.lat();
			          parent.document.getElementById('lon').value = document.getElementById('lon').value = latlng.lng()
		        	 });
       
    		   	map.addOverlay(ObjectMarker);
			ObjectMarker.hide();
 
		geoXml = new GGeoXml( sKmzFile );
		
		map.addOverlay(geoXml);


	      }
	    }
	 
	 
 </script>

<html>	 
	<body   style="margin:0; padding:0;" onload="initialize();"> 
	
		<form id="mapData" name="mapdata" onsubmit="return( false );" >
		
			<input type="hidden" value="<?=$nLanMin; ?>" 	id="nLanMin"  name="nLanMin"/>
			<input type="hidden" value="<?=$nLanMax; ?>" 	id="nLanMax"   name="nLanMax"/>
			<input type="hidden" value="<?=$nLatMin; ?>"	id="nLatMin"   name="nLatMin"/>
			<input type="hidden" value="<?=$nLatMax; ?>" 	id="nLatMax"   name="nLatMax"/>
			<input type="hidden" value="<?=$nCenterLat; ?>" 	id="nCenterLat"  name="nCenterLat"/>
			<input type="hidden" value="<?=$nCenterLan; ?>" 	id="nCenterLan"  name="nCenterLan" />
			<input type="hidden" value="<?=$GoogleKey; ?>" 	id="GoogleKey"  name="GoogleKey" />
			<input type="hidden" value="<?=$sKmzFile; ?>"	id="sKmzFile"  name="sKmzFile" />
			
			<input type="hidden" id="lat" name="lat" />
			<input type="hidden" id="lon" name="lon" />
		
		 </form>
		
		<div id="map_canvas" style="width:100%; height: 650px;" ></div>
	
	</body>
</html>