var bgMap;
var objType="obj";
var currentCar;
var currentObject;
var DirectionService;
var DirectionRenderer;
var objMarker;
var carMarker;
var temp;
var markerObjects={};
var markerCars={};
var markers={};
var markers2={};

sim = {};
sim.init = function() {	
	//sim.loadGMScript();
	$('#map_canvas').css('height',$('body').height()-$("#tblMenu").height()-10);	
	$(window).resize(function(){
		$('#map_canvas').css('height',$('body').height()-$("#tblMenu").height()-10);				
	});
	sim.gMaps();
	
	$.post('simulator_commands.php',{cmd:'getPatrol',		
		region:$("#selRegion option:selected").val()},function(resp){				
		if (!resp)  {					
			alert("Не са намерени патрули в регион "+$("#selRegion").val());
			return;
		}
		var obj = JSON.parse(resp);				
		$("#selPatrol").html('');
		$.each(obj,function(k,v){					
			$("#selPatrol").append("<option value='"+ v.id +"'>"+v.num_patrul+"</option>");
		})													
	});	
	
	$("#selRegion").change(function(){
		$.post('simulator_commands.php',{cmd:'getPatrol',		
			region:$("#selRegion option:selected").val()},function(resp){				
				if (!resp)  {					
					alert("Не са намерени патрули в регион "+$("#selRegion").val());
					return;
				}

				var obj = JSON.parse(resp);					

				$("#selPatrol").html('');

				$.each(obj.patrols,function(k,v){		
					jQuery('<option></option>').attr({
						value:v.id																		
					}).text(v.num_patrul).appendTo('#selPatrol');									
				});		

				$("#selCar").html('');

				for (var m in markerCars) {
					markerCars[m].setMap(null);
				}

				markerCars = {};

				$.each(obj.cars,function(k,v){					
					$("#selCar").append("<option value='"+ v.id +"'>"+v.info+"</option>");					
					markers[v.id]={geo_lat:v.geo_lat, geo_lan:v.geo_lan,id:v.id,info:v.info};
				});
				setTimeout(function(){
					for(var i in markers) {
						markerCars[i] = new google.maps.Marker({
										position: new google.maps.LatLng(markers[i].geo_lat,markers[i].geo_lan), 							
										draggable:true,
										title:markers[i].info,
										map:bgMap,
										id:markers[i].id,
										icon:'img/cars_sim.png'
									});						
						google.maps.event.addListener(markerCars[i],'dragend',function(){
							$.post('simulator_commands.php',{
									cmd:'moveCar',
									idCar : this.id,
									geo_lat : this.position.lat(),
									geo_lan:this.position.lng()								
							})												
						});
						google.maps.event.addListener(markerCars[i],'click',function(){
							currentCar=this;
							if (currentObject) {
								var dsRequest = {
									origin: new google.maps.LatLng(currentCar.position.lat(),currentCar.position.lng()),
									destination: new google.maps.LatLng(currentObject.position.lat(),currentObject.position.lng()),
									travelMode:'DRIVING'
								};
								DirectionService.route(dsRequest,function(directions,status){
									DirectionsRenderer.setDirections(directions);
									console.log(directions)

								});
							}
						});
					}
				},0);
			});
			
		});	
	
	$("#txtObjNum").blur(function(){					
		if ($(this).val()=="") {
			alert("Не сте ибрали номер на обект");
			return;
		}
		$.post('simulator_commands.php',{cmd:'getMsg',
			objNum:$(this).val(),
			region:$("#selRegion option:selected").val()},function(resp){				
			if (!resp)  {					
				alert("Не намерен обект с този номер в регион "+$("#selRegion").val());
				return;
			}
			var obj = JSON.parse(resp);				
			$("#selMsg").html('');
			$.each(obj,function(k,v){					
				jQuery('<option></option>').attr({
					value:v.id,
					msg:v.msg,
					alarm_code:v.code,
					alarm:v.alarm
				}).text(v.msg).appendTo('#selMsg');
				
				//$("#selMsg").append("<option value='"+ v.id +"' alarm_code='"+v.code+"'>"+v.msg+"</option>");
			})													
		});						
	});
	
	$('#btnAddObjectToMap').click(function(){
		if ($("#txtObjNum").val()=="") {
			alert("Не сте ибрали номер на обект");
			return;
		}
		$.post('simulator_commands.php',{
			cmd:'addObject',
			id_msg:$("#selMsg").val(),
			objNum:$('#txtObjNum').val(),
			region:$("#selRegion").val(),
			alarm_code:$("#selMsg option:selected").attr('alarm_code'),
			alarm:$("#selMsg option:selected").attr('alarm'),
			msg:$("#selMsg option:selected").attr('msg')
		},function(resp){			
			if (!resp) {
				console.log("empty")
				return;
			}
			var obj = JSON.parse(resp);	
//			if (markerObjects.hasOwnProperty(obj[0].id)) {
//				
//				return;
//			}
			$.each(obj,function(k,v){										
				markers2[v.id]={geo_lat:v.geo_lat, 
								geo_lan:v.geo_lan,
								id:v.id,
								num:v.num,
								name:v.name,
								address:v.address};
			});			
			
			setTimeout(function(){				
				for(var i in markers2) {
					markerObjects[i] = {
						num:     markers2[i].num, 
						name:    markers2[i].name, 
						address: markers2[i].address,
						marker:  new google.maps.Marker({
										position: new google.maps.LatLng(markers2[i].geo_lat,markers2[i].geo_lan), 
										title:markers2[i].name + "  "+$("#selMsg option:selected").text(), 
										map:bgMap,
										icon:"img/house.png"
									})				
					}					
					google.maps.event.addListener(markerObjects[i].marker,'click',function(){						
						currentObject=this;
						if (currentCar) {
							var dsRequest = {
								origin: new google.maps.LatLng(currentCar.position.lat(),currentCar.position.lng()),
								destination: new google.maps.LatLng(currentObject.position.lat(),currentObject.position.lng()),
								travelMode:'DRIVING'
							};							
							DirectionService.route(dsRequest,function(directions,status){
								DirectionsRenderer.setDirections(directions);								
							});
						}
					})
				}
			},0);
		});
	});
	
	$('#btnAddCarRoadList').click(function(){
		$.post('simulator_commands.php',{
				cmd: 'createRoadList',
				id_auto: $('#selCar').val(),
				id_patrol: $('#selPatrol').val()
			},function(resp){
			
		});
	});
	
	$('#btnDelCarRoadList').click(function(){
		if (!$('#selCar').val()) {
			alert('Изберете автопатрул');
			return;
		}
		$.post('simulator_commands.php',{
			cmd: 'closeRoadList',
			id_auto: $('#selCar').val(),
			id_patrol: $('#selPatrol').val()
		});
	});
	$("#btnRefresh").click(function(){
		window.location.href="";
	});
}
sim.loadGMScript	= function() {
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "http://maps.google.com/maps/api/js?key=AIzaSyAgMeepfn7LP4uw75BUQ8Q79tfuBs4ouKw&callback=sim.gMaps";
	document.body.appendChild(script);
}
sim.gMaps			= function() {        
        var latlng = new google.maps.LatLng(43.270456,26.934013);
        var myOptions = {
          zoom: 14,
          zoomControl: false,
          panControl: false,
          streetViewControl: false,
          mapTypeControl: false,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        bgMap = new google.maps.Map($("#map_canvas")[0],myOptions);        
	   //google.maps.event.addListener(bgMap,'click',sim.getCoordinates);
	   DirectionService = new google.maps.DirectionsService();
	   DirectionsRenderer = new google.maps.DirectionsRenderer();
	   DirectionsRenderer.setMap(bgMap);
        //google.maps.event.trigger(bgMap,'resize');     
  }

//sim.getCoordinates = function(point) {
//	if (objType=="obj") {
//		oObjects.lat = point.latLng.lat();
//		oObjects.lng = point.latLng.lng();
//		var markerPos = new google.maps.LatLng(point.latLng.lat(),point.latLng.lng());
//		objMarker = new google.maps.Marker({position:markerPos,draggable:true});
//		google.maps.event.addListener(objMarker,'click',  function(){console.log(this.position.lat())})
//		google.maps.event.addListener(objMarker,'dragend',function(){console.log(this.position.lat())})
//		objMarker.setMap(bgMap);
//	} else if (objType=="car") {
//		oCars.lat = point.latLng.lat();
//		oCars.lng = point.latLng.lng();
//	}
//	
//}
