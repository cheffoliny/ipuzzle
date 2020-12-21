var wCont;
var wContW;
var wContH;
var bgMap;
var scrollTimer;
var oRegion = {};
var idReg;
var currentFrame = -1;
var showRangeFrame;
var sizeLevel = 20;
var startTimeSec;
var bPlay = false;

function empty( mVar )
{
	var emptyArray = new Array();
	
	return ( mVar == null ) || ( mVar == "" ) || ( mVar == emptyArray ) || ( mVar == "undefined" ) || ( mVar == 'NaN' ) || ( mVar == 0 );
}

pl					= {};
pl.extend			= function(subClass, baseClass) {
   function inheritance() {}
   inheritance.prototype = baseClass.prototype;
   subClass.prototype = new inheritance();
   subClass.prototype.constructor = subClass;
   subClass.baseConstructor = baseClass;
   subClass.superClass = baseClass.prototype;
}
pl.init				= function(){
    this.extend(pl.objectShape, pl.baseShape);
    this.extend(pl.carShape, pl.baseShape);
    this.extend(pl.waypointShape, pl.baseShape); 
	
	var _height = $('body').height()-70 + 'px';
	$("#activeRegion").attr("height",_height);
	$('div.wcontent').css('height',_height);
	$('div.map_canvas').css('height',_height);
	//$('#timeLineWrapper').css('width',$('#tdTLW').width());
	wContW = $('div.wcontent').width();
    wContH = $('div.wcontent').height();
	wCont = pl.initSVG($('div.wcontent > div'));	
	$(window).resize(function(){		
		var _height = $('body').height()-70 + 'px';
		$("#activeRegion").attr("height",_height);
		$('div.wcontent').css('height',_height);
		$('div.map_canvas').css('height',_height);
		wContW = $('div.wcontent').width();
		wContH = $('div.wcontent').height();
		wCont._svg.attributes.viewBox.value = "0,0,"+wContW+","+wContH;	 
		wCont._svg.attributes.height.value = wContH;	 
		wCont._svg.attributes.width.value = wContW;
		pl.autoZoom();		
	});
	
	pl.loadGMScript();		
	
	$("img[name=btn_reverse]").mouseenter(function(){	
		$(this).attr('src','img/'+$(this).attr('name').split('_')[1]+'.png');
	}).mouseleave(function(){ 
		$(this).attr('src','img/'+$(this).attr('name').split('_')[1]+'_grey.png');
	}).click(function(){
		currentFrame = 0;		
		bPlay = false;
		$("#timeLine div[index="+currentFrame+"]").click();
	});
	
	$("img[name=btn_forward]").mouseenter(function(){	
		$(this).attr('src','img/'+$(this).attr('name').split('_')[1]+'.png');
	}).mouseleave(function(){ 
		$(this).attr('src','img/'+$(this).attr('name').split('_')[1]+'_grey.png');
	}).click(function(){
		currentFrame = oRegion.events.length - 1;
		bPlay = false;
		$("#timeLine div[index="+currentFrame+"]").click();
	});
	
	$("img[name=btn_play]").mouseover(function(){		
		$(this).attr('src','img/' + (bPlay ? 'stop' : 'play') + '.png');
	}).mouseout(function(){
		(bPlay) && ($(this).attr('src','img/stop.png')) || ($(this).attr('src','img/play_grey.png'));		
	}).click(function(){
		bPlay = !bPlay;	
		pl.play(currentFrame,oRegion.events.length-1,true,true);				
		$(this).attr('src','img/' + (bPlay ? 'stop' : 'play') + '.png');
	});
	
	$("img[name=btn_wp]").click(function(){		
		oRegion.visibles.wp=!oRegion.visibles.wp;		
		var img = oRegion.visibles.wp ? 'img/wp.png': 'img/wp_grey.png';
		$(this).attr('src',img);
		for (var wp in oRegion.wpSVG) {
			if (oRegion.visibles.wp) {
				oRegion.wpSVG[wp].show();
			} else {
				oRegion.wpSVG[wp].hide();
			}
		}
		pl.autoZoom();
	});
	
	document.onkeydown = function(e){		
		switch(e.keyCode) {
			case 32:
				$("img[name=btn_play]").click();
			break;
			case 70:
				$("img[name=btn_forward]").click();
			break;
			case 82:
				$("img[name=btn_reverse]").click();
			break;
			case 65:
				$("img[name=btn_wp]").click();
			break;
			case 39:
				if (!bPlay && currentFrame < oRegion.events.length - 1) {
					$("#timeLine div[index="+(currentFrame + 1)+"]").click();					
				}
			break;
			case 37:
				if (!bPlay && currentFrame >= 0) {
					$("#timeLine div[index="+(currentFrame - 1)+"]").click();	
				}
			break;
		}							
	}
	//$("#timeLine").draggable({ axis: 'x' });
	//ID_ALARM=193;
	if (ID_ALARM || ID_ALARM_PATRUL) pl.serverCall({id_alarm:ID_ALARM,id_alarm_patrul:ID_ALARM_PATRUL,layer_type:LAYER_TYPE,id_contract:ID_CONTRACT },pl.draw);
}
pl.initSVG			= function(svgBox){
    $(svgBox).svg({settings : {'width':wContW,'height':wContH,'preserveAspectRatio':'xMinYMax','viewBox':'0 0 '+wContW+' '+wContH}});	
    return $(svgBox).svg('get');
}
pl.serverCall		= function(data,success,context,error) {	
	jQuery.ajax({
		type:'POST',
		async:true,
		url:'player_admin_api.php',
		data:{
			request:JSON.stringify({data:data})
		},
		dataType:'json',
		error:function(XMLHttpRequest, textStatus, errorThrown) {
			alert('connection error');
		},
		success:function(response, textStatus, XMLHttpRequest) {
			if (response.type == 'error') {
				if(typeof(error) == 'function') {
					error.apply(context || {},[response.data]);
				} else {
					alert(response.data.message);
				}
			} else {
				if(typeof(success) == 'function') success.apply(context || {},[response.data]);
			}
		},
		timeout: 10000
	});
}
pl.draw				= function(resp) {
	oRegion = resp;
	oRegion.visibles = {map:true,wp:false};
	oRegion.objSVG = {};
	oRegion.carSVG = {};
	oRegion.wpSVG  = {};

	for (var wp in oRegion.wp) {
		oRegion.wpSVG[wp] = new pl.waypointShape(wCont,oRegion.wp[wp].name, oRegion.wp[wp].description,0,0,false);
		oRegion.wpSVG[wp].geo_lat = oRegion.wp[wp].geo_lat;
		oRegion.wpSVG[wp].geo_lan = oRegion.wp[wp].geo_lan;		
	}
	
	//startTimeSec = new Date(oRegion.object.alarm_time).getTime()/1000;
	startTimeSec = new Date(oRegion.object.start_time).getTime()/1000;
	//vzemam start_time 
	
	var tbl = document.createElement("table");
	var drawTimeLineIcon = function(type) {
		var icon = document.createElement("img");		
		(type=="object") && ($(icon).attr({'src':'img/house.png','width':20,'height':20})) || ($(icon).attr({'src':'img/cars.png','width':20,'height':20}));		
		return icon;
	}	
	$(tbl).html("<tr><td nowrap='nowrap'></td></tr>").attr({'border':0,'width':'100%','height':'100%','cellpadding':0,'cellspacing':0}).css({'width':'100%','height':'100%'});
	
	for(var i=0;i<resp.events.length;i++) {		
		var icon;
		
		//(resp.events[i].tech_status != "start" || resp.events[i].tech_status != "arrival") && (icon = drawTimeLineIcon("auto")) || (icon = drawTimeLineIcon("object"));
		(resp.events[i].id_auto!=0) && (icon = drawTimeLineIcon("auto")) || (icon = drawTimeLineIcon("object"));																				
		$(icon).attr('title',resp.events[i].alarm_time.split(" ")[1]);
		var _el = document.createElement("div");		
		$(_el).css({'float':'left','width':'40px','height':'100%','text-align': 'center','border-right':'1px solid grey'})			
			  .attr({index:i,drawn:0})
			  .append(icon)			 
			  .click(function(){
					var start = 0;				  
					var end = $(this).attr('index');	
					var clear = true;
					if ($(this).attr("index") > currentFrame) {
						start = currentFrame + 1;
						clear = false;
					}			  					
					$.each($("#timeLine div"),function(index,el){						
						if (end>=index) {							
							$(el).addClass("drawnFrame");
						} else {
							$(el).removeClass("drawnFrame");
						}
					});
					pl.play(start,end,clear);
			  });				  
		$(tbl).find("td").append(_el);
	}
	$("#btnScrollRight").css("background-image","url('img/scr_right_grey.png')");	
	if ($("#timeLine").width()>$("#timeLineWrapper").width()) {
		$("#btnScrollLeft").css("background-image","url('img/scr_left.png')");
	} else {
		$("#btnScrollLeft").css("background-image","url('img/scr_left_grey.png')");
	}		
	
	$("#timeLine").append(tbl);	

	$("#timeLine").mousedown(function(ed){				
		ed.stopImmediatePropagation();
		ed.preventDefault();
		var startPos =  ed.layerX		
		$(this).mousemove(function(em){	
			pl.scrollTimeLine(em.layerX - startPos);			
		});		
	}).mouseup(function(){
		$(this).unbind('mousemove');
	}).mouseleave(function(){
		$(this).unbind('mousemove');
	});

	$("#btnScrollLeft").mousedown(function(){
		scrollTimer = setInterval(function(){pl.scrollTimeLine(-2 - 20);},5);			
	}).mouseup(function(){
		clearTimeout(scrollTimer);
		scrollTimer = null;
	});
	$("#btnScrollRight").mousedown(function(){
		scrollTimer = setInterval(function(){pl.scrollTimeLine(2-20);},5);	
	}).mouseup(function(){
		clearTimeout(scrollTimer);
		scrollTimer = null;
	});
	
	pl.autoZoom(); // първото показване
}
pl.scrollTimeLine	= function(shift) {	
	//var m = em.offsetX - startPos;		
	if ($("#timeLine").offset().left + shift < 0 && $("#timeLine").width()>$("#timeLineWrapper").width()) {		
		$("#btnScrollLeft").css("background-image","url('img/scr_left.png')");
		$("#timeLine").css('left',$("#timeLine").offset().left + shift + "px");
		if (Math.abs($("#timeLine").offset().left)+$("#timeLineWrapper").width()+20>$("#timeLine").width()) {
			$("#timeLine").css('left',$("#timeLineWrapper").width()-$("#timeLine").width());
			$("#btnScrollRight").css("background-image","url('img/scr_right_grey.png')");
			return;
		} else {
			$("#btnScrollRight").css("background-image","url('img/scr_right.png')");
		}
	} else {
		$("#timeLine").css('left',0);
		$("#btnScrollLeft").css("background-image","url('img/scr_left_grey.png')");
	}	
}
pl.play				= function(start,end,clear,auto,stop) {
	auto = auto || false;
	var timer;	
	var render = function() {
		if (clear) {			
			for (var o in oRegion['objSVG']) oRegion['objSVG'][o].destroy();			
			for (var c in oRegion['carSVG']) oRegion['carSVG'][c].destroy();			
		}
		for(;start<=end;start++) {		
			currentFrame = start;			
			//if (oRegion.events[start].id_auto==="0") {
			//oRegion.events[start].tech_status == "arrival" ||
            console.log(oRegion.object.id_object);
            console.log(oRegion.events[start].admin_status);
			if ( oRegion.events[start].admin_status == "arrival" || oRegion.events[start].admin_status == "start" ) {

				if (!oRegion['objSVG'].hasOwnProperty(oRegion.object.id_object)) {
					oRegion['objSVG'][oRegion.object.id_object] = new pl.objectShape(wCont, oRegion.object.obj_name, 0, 0, true);
					oRegion['objSVG'][oRegion.object.id_object].geo_lat = oRegion.object.obj_geo_lat;
					oRegion['objSVG'][oRegion.object.id_object].geo_lan = oRegion.object.obj_geo_lan;
					oRegion['objSVG'][oRegion.object.id_object].id = oRegion.object.id_object;
				} else {
					oRegion['objSVG'][oRegion.object.id_object].show();
				}
				oRegion['objSVG'][oRegion.object.id_object].setStatus(oRegion.events[start].alarm_status);
				//oRegion['objSVG'][oRegion.object.id_object].setStatus(oRegion.events[start].tech_status);
				//oRegion['objSVG'][oRegion.object.id_object].setStatus("Статус");
				
				
			} else {
				var _idAuto = oRegion.events[start].id_auto;
				if (!oRegion['carSVG'].hasOwnProperty(oRegion.events[start].id_auto)) {
					//debugger;
					oRegion['carSVG'][oRegion.events[start].id_auto] = new pl.carShape(wCont,"car_"+_idAuto,0,0,true);
					oRegion['carSVG'][oRegion.events[start].id_auto].id = oRegion.events[start].id_auto;	
					oRegion['carSVG'][oRegion.events[start].id_auto].callsign = oRegion.events[start].patrul_num;
					oRegion['carSVG'][oRegion.events[start].id_auto].regnum = oRegion.events[start].auto_reg_num;
				} else {
					oRegion['carSVG'][oRegion.events[start].id_auto].show();
				}
				
				oRegion['carSVG'][_idAuto].geo_lat = oRegion.events[start].patrul_geo_lat;
				oRegion['carSVG'][_idAuto].geo_lan = oRegion.events[start].patrul_geo_lan;
				oRegion['carSVG'][_idAuto].statusGeo = parseInt(oRegion.events[start].status_geo);
				oRegion['carSVG'][_idAuto].statusConnection = parseInt(oRegion.events[start].status_connection);
				oRegion['carSVG'][_idAuto].statusService = parseInt(oRegion.events[start].status_service);
				oRegion['carSVG'][_idAuto].distance = oRegion.events[start].distance;
				//debugger;				
				//oRegion['carSVG'][_idAuto].setStatus(oRegion.events[start].alarm_status);
				oRegion['carSVG'][_idAuto].setStatus(start);
				//oRegion['carSVG'][_idAuto].setStatus(oRegion.events[start].tech_status);
				//console.log(oRegion.events[start]);
				
			}
			//pl.gpsRecalc('all');
			var d = oRegion.events[start].alarm_time.split(" ")[0].split("-").reverse().join(".");
			var h = oRegion.events[start].alarm_time.split(" ")[1];			
			$("#clock").html(d+"&nbsp;&nbsp&nbsp"+h);			
			var state;
			switch (oRegion.events[start].tech_status) {
				/*
				case "alarm":
					state = "Обект "+oRegion.object.obj_num+" ";
				break;
				case "update":
					state =  "Обект "+oRegion.object.obj_num+" ";
				break;
				*/
				case "cancel":
					//state = "Обект "+oRegion.object.obj_num+" отмяна - " + (oRegion.alarmReasons[oRegion.events[start].id_reason].name || "") + " ";
					state = "Отказ";//+oRegion.object.obj_num+" отмяна - " + (oRegion.alarmReasons[oRegion.events[start].id_reason].name || "") + " ";
				break;
				/*
				case "notify":
					state =  "Обект "+oRegion.object.obj_num+" уведомен ";
				break;
				case "announce":
					state = "Патрул "+oRegion.events[start].id_patrul+" анонс ";
				break;
				*/
				case "position":
					state = "Позиция ";//+oRegion.events[start].id_patrul+" позиция ";
				break;
				case "start":
					//state = "Патрул "+oRegion.events[start].id_patrul+" реагира ";
					state = "Начало на движение ";//+oRegion.events[start].id_patrul+" реагира ";
				break;
				/*
				case "car_free":
					state = "Патрул "+oRegion.events[start].id_patrul+" свободен ";
				break;
				case "refusal_car":
					state = "Патрул "+oRegion.events[start].id_patrul+" отказ " + oRegion.alarmReasonsCancel[oRegion.events[start].id_reason].name;
				break;
				*/
				case "arrival":
					//state = "Патрул "+oRegion.events[start].id_patrul+" визуален контакт ";
					state = "На обекта " ;//+oRegion.events[start].id_patrul+" визуален контакт ";
				break;
				/*
				case "reason":
					state = "Патрул "+oRegion.events[start].id_patrul+" причина - " + (oRegion.alarmReasons[oRegion.events[start].id_reason].name || "")+" ";
				break;
				*/
			}
			
			//$("#subtitles").html(state + oRegion.events[start].alarm_name);
			$("#subtitles").html(state);
			if(!empty(oRegion['objSVG'][oRegion.object.id_object])) {							
				oRegion['objSVG'][oRegion.object.id_object].setTime();
			}
			pl.autoZoom();
		}		
	}		
	
	if (auto) {
		timer = setInterval(function(){
			if (start<=end && bPlay) {
				$("#timeLine div[index="+start+"]").click();
				start++;
			} else {					
				clearInterval(timer);
				timer = null;
				bPlay = false;
				$("img[name=btn_play]").attr('src','img/' + (bPlay ? 'stop' : 'play') + '_grey.png');
			}			
		},1000);
	} else {
		render();
	}		
}
pl.loadGMScript		= function() {
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=pl.gMaps";
	document.body.appendChild(script);
}	
pl.gMaps			= function() {
	var latlng = new google.maps.LatLng(43.270456,26.934013);
	var myOptions = {
		zoom: 14,
		zoomControl: false,
		panControl: false,
		streetViewControl: false,
		mapTypeControl: false,
		draggable : false,
		center: latlng,			
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	bgMap = new google.maps.Map($("#map_canvas")[0],myOptions);  
}
pl.bgMapMakeBounds	= function() {
	//debugger;
	var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(oRegion.zoomBounds.s,oRegion.zoomBounds.w),
					new google.maps.LatLng(oRegion.zoomBounds.n,oRegion.zoomBounds.e)
	);
	if (!bounds) return false;
	return bounds;
}
pl.gpsRecalc		= function(layer,what) {	
	var mapLeft		= oRegion.visibles.map ? bgMap.getBounds().getSouthWest().lng() : oRegion.zoomBounds.w; 
	var mapTop		= oRegion.visibles.map ? bgMap.getBounds().getNorthEast().lat() : oRegion.zoomBounds.n;                         
	var mapRight	= oRegion.visibles.map ? bgMap.getBounds().getNorthEast().lng() : oRegion.zoomBounds.e;
	var mapBottom	= oRegion.visibles.map ? bgMap.getBounds().getSouthWest().lat() : oRegion.zoomBounds.s;                
	var ratioX	= pl.gpsDistance(0,0,mapLeft,mapRight)/wContW; //oLayers[layer.region]._container.clientWidth;
	var ratioY	= pl.gpsDistance(mapTop,mapBottom,0,0)/wContH; //oLayers[layer.region]._container.clientHeight;   
    
//	if (what=="wp" || what=="all")
	$.each(oRegion.wpSVG,function(k,v){		
		var windY = oRegion.wpSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = oRegion.wpSVG[k].geo_lan < mapLeft ? -1 : 1;
		var sX = pl.gpsDistance(0,0,mapLeft,oRegion.wpSVG[k].geo_lan) / ratioX * windX; 
		var sY = pl.gpsDistance(mapTop,oRegion.wpSVG[k].geo_lat,0,0) / ratioY * windY;        
		oRegion.wpSVG[k].move(sX,sY);        
	});
//	if (what=="obj" || what=="all")
	$.each(oRegion.objSVG,function(k,v){         
		var windY = oRegion.objSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = oRegion.objSVG[k].geo_lan < mapLeft ? -1 : 1;	 
		var sX = pl.gpsDistance(0,0,mapLeft,oRegion.objSVG[k].geo_lan) / ratioX * windX; 
		var sY = pl.gpsDistance(mapTop,oRegion.objSVG[k].geo_lat,0,0) / ratioY * windY;
		oRegion.objSVG[k].move(sX,sY);	   	   
		//oRegion.opSVG.move(sX,sY,k);
	});
//	if (what=="car" || what=="all")
	$.each(oRegion.carSVG,function(k,v){
		//console.log(oRegion.carSVG[k].geo_lat);
		var windY = oRegion.carSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = oRegion.carSVG[k].geo_lan < mapLeft ? -1 : 1;	 
		var sX = pl.gpsDistance(0,0,mapLeft,oRegion.carSVG[k].geo_lan) / ratioX * windX; 
		var sY = pl.gpsDistance(mapTop,oRegion.carSVG[k].geo_lat,0,0) / ratioY * windY;
		var aPointsPixel=[];
		//console.log(oRegion.carSVG[k].aPointsGeo,"oRegion.carSVG[k].aPointsGeo");
		//console.log(k);
		for (var i in oRegion.carSVG[k].aPointsGeo) {
			//console.log(oRegion);
			var windXP = oRegion.carSVG[k].aPointsGeo[i].geo_lan < mapLeft ? -1 : 1;
			var windYP = oRegion.carSVG[k].aPointsGeo[i].geo_lat > mapTop  ? -1 : 1;
			var px = pl.gpsDistance(0,0,mapLeft,oRegion.carSVG[k].aPointsGeo[i].geo_lan) / ratioX * windXP; 
			var py = pl.gpsDistance(mapTop, oRegion.carSVG[k].aPointsGeo[i].geo_lat,0,0) / ratioY * windYP;
			aPointsPixel.push({px:px,py:py});
		}	   
		oRegion.carSVG[k].move(sX,sY,false,aPointsPixel);
	});					
	
}
pl.gpsDistance		= function(lat1,lat2,lan1,lan2) { 
    var R = 6378137 ; // m
    //var R = 6371; // km
    var d = Math.acos(Math.sin(lan1*Math.PI/180)*Math.sin(lan2*Math.PI/180) + 
            Math.cos(lan1*Math.PI/180)*Math.cos(lan2*Math.PI/180) *
            Math.cos(lat2*Math.PI/180-lat1*Math.PI/180)) * R;
    
    return d;
}
pl.autoZoom			= function() {		
	var west;
	var east;
	var north;
	var south;	
	var ratio = wContW/wContH;
	var one  = 0.000012; //1 metyr
	var minSizeRad = 0.0012;
	var aWE = [];
	var aNS = [];
	
	//debugger;
	
//	//obekti
	//for (var objId in geoLayers[idReg].objSVG) {	
		aWE.push(oRegion.object.obj_geo_lan);
		aNS.push(oRegion.object.obj_geo_lat);
	//}
//	//koli
	for (var carId in oRegion.carSVG) {	
		
		aWE.push(oRegion.carSVG[carId].geo_lan);
		aNS.push(oRegion.carSVG[carId].geo_lat);
		
		if (oRegion.carSVG[carId].aPointsGeo.length) {
			for(var i=0;i<oRegion.carSVG[carId].aPointsGeo.length;i++) {
				aWE.push(oRegion.carSVG[carId].aPointsGeo[i].geo_lan);
				aNS.push(oRegion.carSVG[carId].aPointsGeo[i].geo_lat);
			}
		}
	}	
	//stoqnki
	for (var wpId in oRegion.wp) {
		if (oRegion.visibles.wp) {
			aWE.push(oRegion.wp[wpId].geo_lan);
			aNS.push(oRegion.wp[wpId].geo_lat);
		}
	}
	
	if (aWE.length && aNS.length) {
		west  = Math.min.apply(Math, aWE);
		east  = Math.max.apply(Math, aWE);
		north = Math.max.apply(Math, aNS);
		south = Math.min.apply(Math, aNS);
				
		if(Math.abs(west-east) < minSizeRad) {
			west -= (east-west + minSizeRad)/2;
			east += (east-west + minSizeRad)/2;
		}
		if(Math.abs(north-south) < minSizeRad) {
			south -= (north-south + minSizeRad)/2;
			north += (north-south + minSizeRad)/2;
		}
	
		west  -= (east - west)   * 0.05;
		east  += (east - west)   * 0.05;
		north += (north - south) * 0.08;
		south -= (north - south) * 0.08;
	

		if (Math.abs(east-west)/Math.abs(north-south) <= ratio) {
			var heightRad = Math.abs(east-west) / ratio;
			north += heightRad * 0.5;
			south -= heightRad * 0.5;
	//		north += (Math.abs(east-west)/ratio - Math.abs(north-south))*0.5;
	//		south -= (Math.abs(east-west)/ratio - Math.abs(north-south))*0.5;
		} else {
			var widthRad = Math.abs(north-south) * ratio;
			east += widthRad * 0.5;
			west -= widthRad * 0.5;
	//		west -= (Math.abs(north-south)*ratio - Math.abs(east-west))*0.5;
	//		east += (Math.abs(north-south)*ratio -  Math.abs(east-west))*0.5;
		}	
		oRegion.zoomBounds = {
			w:west,
			e:east,
			n:north,
			s:south
		}	
	} 
		
	if (oRegion.visibles.map) {
		bgMap.fitBounds(pl.bgMapMakeBounds());				
//		if (geoLayers[idReg].visibles.wp || !geoLayers[idReg].visibles.cars && !geoLayers[idReg].alarm) bgMap.setZoom(bgMap.getZoom()+1);
		//if (oRegion.visibles.wp) bgMap.setZoom(bgMap.getZoom()+1);
	}
	
	pl.gpsRecalc();
}
pl.baseShape					= function(x,y,shape) {
    this.id;
    this.idRegion;
    this.num;
    this.name;    
    this.X = x;
    this.Y = y;
    this.newX = x;
    this.newY = y;        
    this.shape = shape;    
    this.region_name;    
    this.visible;
}
pl.baseShape.prototype.hide		= function() {$(this.shape).attr('display','none');};
pl.baseShape.prototype.show		= function() {$(this.shape).attr('display','');};
pl.baseShape.prototype.goLast	= function() {
	(!$(this.shape).is(":last-child")) && ($(this.shape).insertAfter($("#joker").find("svg > *:last-child")));
}
pl.objectShape		= function(svg,name,x,y,draw) {
	var circle;
	var gReason;
	var reasonRect;
	var reasonText;
	var txtTimer;
	var range = null;
	var that = this;
	var _g = svg.group({name:"obj",display:'none'});
	pl.objectShape.baseConstructor.call(this,x,y,_g);
	
	this.create = function() {
		if (circle) return;
		range = svg.circle(_g,0,0,sizeLevel*2,{'fill-opacity': "0.1",'fill': 'blue',display:'none'});
		circle = svg.circle( _g,0,0,sizeLevel/2,{'fill-opacity': "0.5",'stroke-opacity': "0.5",'fill': 'white','stroke': 'blue','strokeWidth': 1});               
		gReason	= svg.group(_g,{name:name+"_reason",display:'none'});
		reasonRect = svg.rect(gReason,0,0,148,20,5,5,{'class':'bgRect'});
		reasonText = svg.text(gReason,0,0,'',{'class':'reasonText',name:name+"_txtreason"}); 
		txtTimer = svg.text(_g,0,0,'0:00',{fill:'green',style:'font-size:10px;',name:name+"_txttimer"}); 
		$(circle).mouseenter(function(){that.goLast();});
	}
	
	this.showReason = function() {											
			$(reasonRect).attr('x',$(circle).attr('cx') - 75);
			$(reasonRect).attr('y',$(circle).attr('cy') - 45);				
			$(reasonText).attr('x',$(circle).attr('cx'));
			$(reasonText).attr('y',$(circle).attr('cy') - 30);				
			$(reasonText).text(oRegion.alarmReasons[oRegion.events[currentFrame].id_reason].name);		
			$(gReason).attr('display','');				
    }
	
	this.setStatus = function(status) {
		switch (status) {
			case "alarm":
				
			break;
			case "update":
				
			break;
			case "cancel":
			//case "arrival":
				this.showReason();
				this.showRange(false);
			break;
		}
	}
	
	this.setTime = function(){ 	
		var time = new Date(oRegion.events[currentFrame].alarm_time).getTime()/1000 - startTimeSec;
		var color = "green";                        
		var minutes = Math.floor(time / 60);
		var seconds = time % 60;
		var sTime = minutes + ":" + (seconds < 10 ? "0"+seconds : seconds);
		//var sTime = oRegion.events[currentFrame].distance; // сетва на обекта инфото
		(time >= oRegion.object.obj_time_alarm_reaction) && (color = "red");
		$(txtTimer).text(sTime).attr('fill',color);
	}
	
	this.showRange = function(show) { $(range).attr('display',show?'':'none'); }
	
	this.move = function(x,y) {
		range.attributes.cx.value=x;
		range.attributes.cy.value=y;
		circle.attributes.cx.value = x;
		circle.attributes.cy.value = y;
		txtTimer.attributes.x.value = x-8;
		txtTimer.attributes.y.value = y+30;
		reasonRect.attributes.x.value = x-75;
		reasonRect.attributes.y.value = y-45;
		reasonText.attributes.x.value = x;
		reasonText.attributes.y.value = y-30;
		this.newX = x;
		this.newY = y;
	}
	
	this.destroy = function() {
		$(this.shape).remove();
		delete oRegion.objSVG[this.id];
	}
	
	this.create();	
	if (draw) this.show();
}
pl.carShape			= function(svg,name,x,y,draw) {
	this.status;
	this.statusGeo;
	this.statusConnection;
	this.statusService;
	this.aPointsGeo = [];
	this.distance;
	this.idObject;
	this.callsign;
	this.regnum;
	
	var car
	var line;
	var txtDistance;
	var shadowStart;
	var lineStart;
	var txtDistance;
	var lineStart;
	var rectNum;
	var txtNum;
	var _g = svg.group({name:name,display:'none'});
	var that = this;
	//debugger;
	pl.carShape.baseConstructor.call(this,x,y,_g);
	
	this.create = function() {
		if (car) return;
		lineStart = svg.polyline(_g,[[this.newX+8,this.newY+2]],{display:'none','class':'lineStart',poY:'13',poX:'8'});
		line = svg.line(_g,0,0,0,0,{'class':'line',display:'none',poX:+sizeLevel/2,poY:+sizeLevel/2}); 
		//line = svg.line(_g,0,0,0,0,{'class':'line',display:'',poX:+sizeLevel/2,poY:+sizeLevel/2}); 
		txtDistance = svg.text(_g,10,10,this.distance || '0.0',{style:'font-size:10px;',name:name+"_txtdist",display:'none',txtDistance: '1'});
		shadowStart = svg.image(_g,0,0, sizeLevel, sizeLevel, "img/car_shadowstart_64.png",{display:'none',shadowStart:'1'});
		rectNum  = svg.rect(_g,0,0,sizeLevel/2,15,5,5,{'class':'bgRect','rectNum':1,poX:-sizeLevel/2,poY:-sizeLevel*1.6});
		txtNum = svg.text(_g,
                    this.X+sizeLevel/2,
                    this.Y-sizeLevel,
                    this.callsign+'',
                    {style:'text-anchor:middle;font-size:10px;',
                     name:name+"_txtnum",
                     txtNum:'1',
                     poX:0,
                     poY: -sizeLevel});
		car =  svg.image(_g,0,0, sizeLevel, sizeLevel, "img/car_free_64.png",{'car':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});                
		
		$(car).mouseenter(function(){          										
			txtNum.textContent = that.callsign +'('+that.regnum+')';            
			rectNum.attributes.width.value  = $(txtNum).width()  + 4;
			rectNum.attributes.height.value = $(txtNum).height() + 4;
			rectNum.attributes.x.value = that.newX - parseInt(rectNum.attributes.width.value)/2;
			that.goLast();
        }).mouseleave(function(){			
            txtNum.textContent = that.callsign;
			rectNum.attributes.width.value  = $(txtNum).width()  + 4;
			rectNum.attributes.height.value = $(txtNum).height() + 4;
			rectNum.attributes.x.value = that.newX - parseInt(rectNum.attributes.width.value)/2;	
        });
	}
	
	this.move = function(x,y,animate,aPointsPixel) {		
		animate = animate || false;		
		var x2,y2;
		//console.log(this.idObject);
		if (this.idObject) {
			
			//console.log(x2);
			//console.log(y2);
			console.log(oRegion['objSVG']);
			x2 = oRegion['objSVG'][this.idObject].newX;
			y2 = oRegion['objSVG'][this.idObject].newY;			
		}		
		
		$(this.shape).find("*").each(function(index,child){ 
			//console.log(child);
			var offsetX = parseInt($(child).attr("poX")) || 0;
			var offsetY = parseInt($(child).attr("poY")) || 0;            
			if ($(child).attr('x1')) {				
				//(right) && (offsetX+=10);
				(animate) && 
				($(child).animate({svgX1 : x + sizeLevel/2, svgY1 : y  + sizeLevel/1.5})) ||				
				($(child).attr("x1",x)) && ($(child).attr("y1",y)) && 
				($(child).attr("x2",x2)) && ($(child).attr("y2",y2));
			} else if($(child).attr("txtDistance")) {                
				(animate) && 
				($(child).animate({svgX: (x + x2) / 2,svgY: (y + y2) / 2})) ||
				($(child).attr("x",(x + x2) / 2).attr("y",(y + y2) / 2));
			} else if($(child).attr("shadowStart")) { 	
				if (aPointsPixel.length) {
					shadowStart.attributes.x.value = aPointsPixel[0].px-sizeLevel/2;
					shadowStart.attributes.y.value = aPointsPixel[0].py-sizeLevel/2;
				}
			} else if ($(child).attr("points")) {
				//console.log("lin1");
				//console.log(aPointsPixel,"aPointsPixel");
				var sPoints="";
				for (var i in aPointsPixel) {														
					sPoints += (aPointsPixel[i].px) + "," + (aPointsPixel[i].py) + " ";					
				}	
				//console.log(lineStart,'LLLSSS');					
				lineStart.attributes.points.value = sPoints;				
			} else if ($(child).attr("rectNum")) {
				rectNum.attributes.width.value = $(txtNum).width() + 4;
				rectNum.attributes.height.value = $(txtNum).height() + 4;				
				$(child).attr("x",x - parseInt(rectNum.attributes.width.value) / 2).attr("y",y + offsetY);													
			} else {
				(animate) &&
				($(child).animate({svgX : x + offsetX, svgY : y + offsetY})) ||									
				($(child).attr("x",x + offsetX).attr("y",y + offsetY));				
				if (y<=10 && $(child).attr("txtNum")) {
					$(child).attr("x", x + offsetX).attr("y", y - offsetY + sizeLevel*1.2);
				}
			}
		});
		this.newX = x;
		this.newY = y;		
	}   	
	
	this.setStatus = function(status) {
		//console.log(status,"1");
		
		
		
		
		
		if(status == 2 )
		{
			//на 2рия път се свързва с обекта
			this.status = "reaction";
			this.idObject = oRegion.object.id_object;
			$(car).attr("href","img/car_start_64.png");
			$(line).removeClass('lineAnnounce').addClass('line').attr('display','');
			$(txtDistance).attr('display','');
			$(shadowStart).attr('display','');
			$(lineStart).attr('display','');
		}
		
		switch (status) {
			
			case "reason" :
			case "car_free" :
				this.status = "free";
				this.idObject = "";
				$(car).attr("href","img/car_free_64.png");
				$(txtDistance).attr('display','none');
				$(shadowStart).attr('display','none');
				$(lineStart).attr('display','none');
				$(line).attr('display','none');
				this.aPointsGeo=[];
			break;
			
			case "start" :
				this.status = "reaction";
				this.idObject = oRegion.object.id_object;
				$(car).attr("href","img/car_start_64.png");
				$(line).removeClass('lineAnnounce').addClass('line').attr('display','');
				$(txtDistance).attr('display','');
				$(shadowStart).attr('display','');
				$(lineStart).attr('display','');
			break;
						
			case "announce":
				this.status = "announce";
				this.idObject = oRegion.object.id_object;
				$(car).attr("href","img/car_announce_64.png");
				$(line).removeClass('line').addClass('lineAnnounce').attr('display','');
				$(txtDistance).attr('display','').text(Math.ceil(this.distance));
				$(shadowStart).attr('display','none');
				$(lineStart).attr('display','none');
			break;
			
			case "position":
				
			break;
			case "arrival":
				oRegion.objSVG[this.idObject].showRange(true);
			break;	
									
		}
				
		!(this.statusGeo  |  !this.statusConnection)	&& $(car).attr("href","img/car_nosat_64.png")	||
		!(!this.statusGeo |   this.statusConnection)	&& $(car).attr("href","img/car_nocon_64.png")	||
		 (!this.statusGeo && !this.statusConnection)	&& $(car).attr("href","img/car_nosatnocon_64.png");		
		 
		 if (this.statusService) {
			 $(car).attr("href","img/car_service_64.png");
			 //service_status_reason 
		 }
		 //console.log(this.distance,'d');
		 //if (this.status=="position") {
		 if (this.status=="reaction") {
			 $(txtDistance).text(Math.ceil(this.distance));
			 //console.log(this.aPointsGeo,"3");
			 this.aPointsGeo.push({geo_lat:this.geo_lat,geo_lan:this.geo_lan});
		 }
		 $(txtNum).text(this.callsign);
		 //br++; // test 
		pl.gpsRecalc();
	}
	
	this.destroy = function() {
		$(this.shape).remove();
		delete oRegion.carSVG[this.id];
	}
		
	this.create();
	if (draw) this.show();
}
pl.waypointShape	= function(svg,name,desc,x,y,draw) {
	//debugger;
	this.geo_lat;
	this.geo_lan;
	this.description;
		
	var nameWP;	
	var descWP;
	var rectName;
	var rectDesc;
	var _g = svg.group({name:name,display:'none'});
	var gNameDesc = svg.group(_g,{display:"none"});
	var wp;
	var that = this;
	
	this.create = function() {    				
		(!name) && (name="Безименна");
		(!desc) &&(desc="Без описание");		
		wp = svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/wp.png",{'wp':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});                
		rectName  = svg.rect(gNameDesc,0,0,0,20,5,5,{'class':'bgRect'});
		nameWP	  = svg.text(gNameDesc,0,0,name,{'class':'reasonText'});		
		rectDesc  = svg.rect(gNameDesc,0,0,0,20,5,5,{'class':'bgRect'});
		descWP	  = svg.text(gNameDesc,0,0,desc,{'class':'reasonText'});		
		$(wp).mouseenter(function(){ 			
			rectName.attributes.width.value = $(nameWP).width() + 4;			
			rectName.attributes.x.value = that.newX - rectName.attributes.width.value / 2;
			if (rectName.attributes.x.value < 0) rectName.attributes.x.value = 2;
			if (rectName.attributes.x.value > wContW - rectName.attributes.width.value) rectName.attributes.x.value = wContW - rectName.attributes.width.value - 2;
			rectName.attributes.y.value = that.newY - sizeLevel - 15;
            rectDesc.attributes.width.value = $(descWP).width() + 4;
			rectDesc.attributes.x.value = that.newX - rectDesc.attributes.width.value / 2;
			if (rectDesc.attributes.x.value < 0) rectDesc.attributes.x.value = 2;
			if (rectDesc.attributes.x.value > wContW - rectDesc.attributes.width.value) rectDesc.attributes.x.value = wContW - rectDesc.attributes.width.value - 2;
			rectDesc.attributes.y.value = that.newY + sizeLevel - 5;
			nameWP.attributes.x.value = that.newX;
			if (nameWP.attributes.x.value  < $(nameWP).width()/2) nameWP.attributes.x.value = $(nameWP).width()/2 + 4;
			if (nameWP.attributes.x.value > wContW - $(nameWP).width()/2) nameWP.attributes.x.value =  wContW - $(nameWP).width()/2 - 4;
			nameWP.attributes.y.value = that.newY - sizeLevel;
			descWP.attributes.x.value = that.newX;	
			if (descWP.attributes.x.value < $(descWP).width()/2) descWP.attributes.x.value = $(descWP).width()/2 + 4;			
			if (descWP.attributes.x.value > wContW - $(descWP).width()/2) descWP.attributes.x.value =  wContW - $(descWP).width()/2 - 4;
			descWP.attributes.y.value = that.newY + sizeLevel + 10;
			that.goLast();
			$(gNameDesc).attr('display','');
        }).mouseleave(function(){
            $(gNameDesc).attr('display','none');			
        });
	}
	
	this.move = function(x,y) {
		$(this.shape).find("*").each(function(index,child){
			var offsetX = parseInt($(child).attr("poX")) || 0;
			var offsetY = parseInt($(child).attr("poY")) || 0;   
			$(child).attr("x",x + offsetX).attr("y",y + offsetY)
		});
		rectName.attributes.width.value = $(nameWP).width() + 4;
		rectName.attributes.x.value = that.newX - rectName.attributes.width.value / 2;
		rectName.attributes.y.value = that.newY - sizeLevel - 15;
		rectDesc.attributes.width.value = $(descWP).width() + 4;
		rectDesc.attributes.x.value = that.newX - rectDesc.attributes.width.value / 2;
		rectDesc.attributes.y.value = that.newY + sizeLevel - 5;
		nameWP.attributes.y.value = that.newY - sizeLevel;
		descWP.attributes.y.value = that.newY + sizeLevel + 10;
		
		this.newX = x;
		this.newY = y;
	}
	
	pl.objectShape.baseConstructor.call(this,x,y,_g);
	this.create();	
	if (draw) this.show(); 
}