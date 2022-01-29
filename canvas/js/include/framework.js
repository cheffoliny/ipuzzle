var oLayers={};
var geoLayers={};
var wSock;
var menuBottomTop;
var sizeLevel = 20;
var bgMap;
var wCont;
var wContW;
var wContH;
var activeSVG;
var activeRegionId;
var lockTaskBar = false;
var isInit = false;
var alarmReasonsPatrol = {};
var alarmReasonsCancelPatrol = {};
var alarmReasonsBypassObject = {};
var alarmReasonsObjectMonitoring = {};
var alarmReasonsAll = {};
var nIDReasonNotify;
var alarm;
var sounds = {};
var disconnectTimeCounter=0;
var down = true;
var dialogOpen = false;
var archiveOpen = false;
var alarmOpen = false;
var serviceBypassOpen = false;
var monitoringOpen = false;
var clickReverse = false;
var bSelector = false;
var regionBounds = [];
var monitoringStatus;
var monitoringInterval;
var selectedObjectId;
var clientCoordinates = false;
var sockBytesReceived = [];
	sockBytesReceived[59] = {len:0,ts:Math.round(new Date().getTime() / 1000)};


jQuery(document).ready(function() {
	
	$("#divNotifications").draggable({containment: $('#activeRegion'), axis: 'y'});
	$("#divAlarmPanics").draggable({containment: $('#activeRegion'), axis: 'y'});
	
	jQuery('#divNotifications').delegate('.notification_row','click',function(evt){
		
		if (evt.target.tagName==="A") return;
		
		if (evt.target.tagName==="IMG")  {
			fw.cancelNotification($(this));
		} else {
			fw.confirmNotification($(this));
		}
	});
	jQuery('#divAlarmPanics').delegate('.alarm_panic_row','click',function(evt){
		
		if (evt.target.tagName==="A") return;
		
		if (evt.target.tagName==="IMG")  {
			fw.cancelAlarmPanic($(this));
		} else {
			fw.confirmAlarmPanic($(this));
		}
	});
	
});

fw = {};

fw.cancelNotification = function(jel) {
	
	var id_notification = jel.attr('id_notification');

	var aData = {};

	aData['id_notification'] = id_notification;
	
	fw.serverCall(
		'notification_cancel', 
		aData, 
		function() {
			fw.alarmNotifications();
		}
	);
}
fw.cancelAlarmPanic = function(jel) {
	
	var id_alarm_panic = jel.attr('id_alarm_panic');

	var aData = {};

	aData['id_alarm_panic'] = id_alarm_panic;
	
	fw.serverCall(
		'alarm_panic_cancel', 
		aData, 
		function() {
			fw.alarmNotifications();
		}
	);
}

fw.confirmNotification  = function(jel) {
	var gsm = jel.find('a').html();
		
	var title = 'Потвърждаване на известяване';
	var content = 'На Телефон ' + gsm + ' ми отговори лицето<br/><input id="notification_face" style="width:250px;margin-top:5px;" type="text" />' ;
	var id_notification = jel.attr('id_notification');

	var aData = {};

	aData['id_notification'] = id_notification;

	dialogOpen = true;
	fw.confirm(
		title,
		content,
		'',
		function() {
			var notification_face = $('#notification_face').val();

			aData['face'] = notification_face;

			fw.serverCall(
				'notification_confirmed', 
				aData, 
				function() {
					fw.alarmNotifications();
				}
			);

			dialogOpen = false;
		},
		function() {
			dialogOpen = false;
		}
	);
}

fw.confirmAlarmPanic = function(jel) {
		
	var title = 'Потвърждаване на паник функция';
	var content = 'Лице активирало паник функцията<br/><input id="alarm_panic_face" style="width:250px;margin-top:5px;" type="text" />' ;
	var id_alarm_panic = jel.attr('id_alarm_panic');

	var aData = {};

	aData['id_alarm_panic'] = id_alarm_panic;

	dialogOpen = true;
	fw.confirm(
		title,
		content,
		'',
		function() {
		
			var alarm_panic_face = $('#alarm_panic_face').val();

			aData['face'] = alarm_panic_face;

			fw.serverCall(
				'alarm_panic_confirmed', 
				aData, 
				function() {
					fw.alarmNotifications();
				}
			);

			dialogOpen = false;
		},
		function() {
			dialogOpen = false;
		}
	);
}

fw.init				= function() {    			
    this.extend(fw.objectShape, fw.baseShape);
    this.extend(fw.carShape, fw.baseShape);
    this.extend(fw.waypointShape, fw.baseShape);    
    this.extend(fw.operatorShape, fw.baseShape);    
	sounds.disconnect = new Audio('sounds/disconnect.mp3');
	sounds.connect = new Audio('sounds/connect.mp3');
	sounds.playOnceDisconnect = true;
	sounds.playOnceConnect = false;
    $('div.wcontent').css('height',$(window).height()-90+'px');
	$(document).bind('contextmenu',function(e){e.preventDefault;return false;})	

	window.onbeforeunload = function(e) {			
		var pkgInit = "close\t"+SESSION+"\t"+ID_PERSON;
		wSock.send(pkgInit);
		wSock.close();
		wSock=null;											
	}	
    $(window).resize(function(){	
		$('div.wcontent').css('height',$('#activeRegion').height()+2+'px');		
		$('div.wcontent').css('width',$('#activeRegion').width()+'px');		
		wContW = $('div.wcontent').width();
		wContH = $('div.wcontent').height();
		if (oLayers[activeRegionId]) {
			oLayers[activeRegionId]._svg.attributes.viewBox.value = "0,0,"+wContW+","+wContH;	 
			oLayers[activeRegionId]._svg.attributes.height.value = wContH;	 
			oLayers[activeRegionId]._svg.attributes.width.value = wContW;
		}		
		fw.gpsRecalc(geoLayers[activeRegionId],'all')		
    });	
	wContW = $('div.wcontent').width();
    wContH = $('div.wcontent').height();    
	$("div[name=conStatus]").click(function(){
		if (wSock.readyState!=1) {				
			wSock.close();
			wSock=null;
			window.location.href="";
		}
	});
	$('body').animate({opacity:1},1000);				
	fw.onKey();
	fw.bottomMenu();
	
	fw.alarmArchive();	
	fw.alarmNotifications();	
	fw.serviceBypass();
	fw.loadGMScript();     
	setTimeout(fw.serverCon,1000);	
    setInterval(fw.clock,1000); 	
}
fw.monitoring		= function() {	
	monitoringOpen = !monitoringOpen;
	if (monitoringOpen) {		
		monitoringStatus = new Stats();
		monitoringStatus.domElement.style.position = 'relative';
		monitoringStatus.domElement.style.float = 'right';
		monitoringStatus.domElement.style.left = '0px';
		monitoringStatus.domElement.style.top = '0px';
		$(monitoringStatus.domElement).appendTo('#statusBar');
		monitoringInterval = setInterval( function () {monitoringStatus.update();}, 1000 / 60 );
		
	} else {
		clearInterval(monitoringInterval);
		$(monitoringStatus.domElement).remove();				
		monitoringInterval = null;
		monitoringStatus = null;
	}
}
fw.refersh			= function() {
	window.location.href="";
}
fw.random			= function(range) {
    return Math.floor(Math.random()*(range-50)+50);
}
fw.bottomMenu		= function() {
    $("td[name=mbb0]").click(function(){ //button Nastrojki       
		var timer, win;
		win = window.open('http://213.91.252.143/isu/page.php?page=set_settings&sess='+SESSION,'_settings','height=800,width=1000,location=0,status=0,toolbar=0');	   	  				
		timer = setInterval(function(){
			if (win && win.closed) {
				clearInterval(timer);
				window.location.href='';
			}
		},1000);
    });
	    
    $("td[name=mbb2] li").click(function(e){
		//viziq
		switch (e.target.name) {
			case "chbCarVisActive":
				if (e.target.checked) {
					geoLayers[activeRegionId].visibles.cars=1;
					$.each(geoLayers[activeRegionId].carSVG, function(){
						(this.car_function == 2 && this.mainOffice == this.idRegion) && this.show();
					});
					$(this).find('img').attr('src','img/cars.png');
				} else {
					$.each(geoLayers[activeRegionId].carSVG, function(){
						(this.status == "free" && this.car_function == 2 && this.mainOffice == this.idRegion) && this.hide();
					});
					geoLayers[activeRegionId].visibles.cars=0;
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/cars_grey.png');
				}
//				fw.gpsRecalc(geoLayers[activeRegionId],'all');
			break;
			case "chbCarVisAll":
				for (var r in geoLayers) {
					if (e.target.checked) {
						geoLayers[r].visibles.cars=1;
						$.each(geoLayers[r].carSVG, function(){
							if (this.car_function == 2 && this.mainOffice == this.idRegion) this.show();
						});
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/cars.png');
					} else {
						$.each(geoLayers[r].carSVG, function(){
							if (this.status == "free" && this.car_function == 2 && this.mainOffice == this.idRegion) this.hide();
						});
						geoLayers[r].visibles.cars=0;
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/cars_grey.png');
					}
//					fw.gpsRecalc(geoLayers[r],'all');
				}				
			break;
			
			case "chbWpVisActive":				
				if (e.target.checked) {					
					//fw.gpsRecalc(geoLayers[activeSVG.attr('name').split("_")[1]],'all');
					$.each(geoLayers[activeSVG.attr('name').split("_")[1]].wpSVG, function(){this.show();})  
					geoLayers[activeRegionId].visibles.wp=1;
					$(this).find('img').attr('src','img/wp.png');
				} else {
					$.each(geoLayers[activeRegionId].wpSVG, function(){this.hide();});
					geoLayers[activeRegionId].visibles.wp=0;
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/wp_grey.png');
				}
//				fw.gpsRecalc(geoLayers[activeRegionId],'all');
			break;
			case "chbWpVisAll":				
				for (var r in geoLayers) {
					if (e.target.checked) {												
						geoLayers[r].visibles.wp=1;
						$.each(geoLayers[r].wpSVG, function(){this.show();});
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/wp.png');
						
					} else {
						$.each(geoLayers[r].wpSVG, function(){this.hide();});
						geoLayers[r].visibles.wp=0;
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/wp_grey.png');
					}
//					fw.gpsRecalc(geoLayers[r],'all');
				}					
			break;
			
			case "chbMapVisActive":
				if (e.target.checked) {
					geoLayers[activeRegionId].visibles.map=true;													
					$("#map_canvas").css('visibility',"visible");
					$(this).find('img').attr('src','img/map.png');
				} else {
					geoLayers[activeRegionId].visibles.map=false;
					$("#map_canvas").css('visibility',"hidden");										
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/map_grey.png');
				}
				//fw.gpsRecalc(geoLayers[activeRegionId],"all");
			break;
			case "chbMapVisAll":
				for (var r in geoLayers) {
					if (e.target.checked) {
						geoLayers[r].visibles.map=true;													
						$("#map_canvas").css('visibility',"visible");
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/map.png');
					} else {
						geoLayers[r].visibles.map=false;
						$("#map_canvas").css('visibility',"hidden");											
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/map_grey.png');
					}
					//fw.gpsRecalc(geoLayers[r],"all");
				}
			break;
			case "chbTGPSActive":
				if (e.target.checked) {
					geoLayers[activeRegionId].visibles.tgps=1;													
					$.each(geoLayers[activeRegionId].carSVG, function(){
						if (this.car_function == 2 && this.mainOffice == this.idRegion) this.showCarTablet(1);
					});
					$(this).find('img').attr('src','img/tablet.png');
				} else {
					geoLayers[activeRegionId].visibles.tgps=0;
					$.each(geoLayers[activeRegionId].carSVG, function(){
						if (this.car_function == 2 && this.mainOffice == this.idRegion) this.showCarTablet(0);
					});
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/tablet_grey.png');
				}				
			break;
			case "chbTGPSVisAll":
				for (var r in geoLayers) {
					if (e.target.checked) {
						geoLayers[r].visibles.tgps=1;													
						$.each(geoLayers[r].carSVG, function(){
							if (this.car_function == 2 && this.mainOffice == this.idRegion) this.showCarTablet(1);
						});
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/tablet.png');
					} else {
						geoLayers[r].visibles.tgps=0;
						$.each(geoLayers[r].carSVG, function(){
							if (this.car_function == 2 && this.mainOffice == this.idRegion) this.showCarTablet(0);
						});
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/tablet_grey.png');
					}					
				}
			break;
		}
		fw.autoZoom(activeRegionId);	  		
    });
    $("td[name=mbb2] > div.menuBottomButton").mouseenter(function(){        
        $(this).find(".submenu").removeClass('submenu-hide');
    });
    $("td[name=mbb2]").mouseleave(function(){
          $(this).find(".submenu").addClass('submenu-hide');
    });
    
    $("td[name=mbb3]").click(function(){  
	   //button obekt
	   if (alarmOpen) return;
	   alarmOpen = dialogOpen = true;
	   var inpSugObj = $("<input id='sugObj' style='width:100%;'/>");
	   var inpObjData  = $("<input type='hidden' id='objData' />");	   
	   var btnToObjectInfo = $("<button style='width:100%;'>Картон</button>")
			.attr('disabled','disabled')
			.click(function(){
				window.open(BASE_URL+"/telenet/page.php?page=object_info&nID="+inpObjData.val(),'_blank','width=1080,height=515,resizable=no'); 
			});
	   var selAlarms = $("<select style='width:100%'></select>").attr('disabled','disabled');
	   var btnFireupAlarm = $("<button style='width:100%;'>Активирай</button>")
			.attr('disabled','disabled')
			.click(function(){	
				for (var r in geoLayers) {
					if (geoLayers[r].objSVG.hasOwnProperty(inpObjData.val())) {
						fw.alert("Системно съобщение","Вече има аларма за обекта");					
						return;
					}
				}								
				var obj = selAlarms.find('option:selected')[0].attributes;
				fw.sendCommand('fireup_alarm',
					{id_msg:obj.value.value,
					 objNum:inpObjData.attr('num'),
					 objID:inpObjData.val(),
					 region:activeRegionId,
					 alarm_code:obj.alarm_code.value,
					 alarm:obj.alarm.value,
					 msg:obj.msg.value},
					function(){},
					function(){},
				'fireup_alarm');
			});
	   var tblHandAlarm = $("<table width='100%'></table>")
						  .append("<tr><td colspan='2'>Обект номер или име</td></tr>")
						  .append("<tr><td width='90%' style='padding-right:4px;'></td><td></td></tr>")
						  .append("<tr><td colspan='2'>Ръчно активиране на аларма</td></tr>")
						  .append("<tr><td width='90%'></td><td></td></tr>");
	   tblHandAlarm.find('tr:eq(1) td:eq(0)').append(inpSugObj);
	   tblHandAlarm.find('tr:eq(1) td:eq(0)').append(inpObjData);
	   tblHandAlarm.find('tr:eq(1) td:eq(1)').append(btnToObjectInfo);
	   tblHandAlarm.find('tr:eq(3) td:eq(0)').append(selAlarms);
	   tblHandAlarm.find('tr:eq(3) td:eq(1)').append(btnFireupAlarm);
	   var dAlarm = $('<div></div>').addClass('objectAlarmDialog')
					   .append(tblHandAlarm)
					   .appendTo('body')
					   .bind('dialogclose',function(){alarmOpen = false;dialogOpen = false;$(this).remove();})
					   .dialog({width:500,title:"Обекти"});	   		
	   fw.suggest(inpSugObj,inpObjData,
			function(){				
				fw.sendCommand('getMsg',{region:activeRegionId,objNum:inpObjData.attr('num'),objID:inpObjData.attr('value')},
					function(resp){
						if (!resp.length) return;						
						selAlarms.html('');
						for (var i=0;i<resp.length;i++) {
							$("<option></option>").attr({
								value:resp[i].id,
								msg:resp[i].msg,
								alarm_code:resp[i].code,
								alarm:resp[i].alarm
							}).text(resp[i].msg).appendTo(selAlarms);
						}
					},
					function(){},
					'getMsg');
				btnToObjectInfo.attr('disabled','');
				btnFireupAlarm.attr('disabled','');
				selAlarms.attr('disabled','');
			},
			function(){
				btnToObjectInfo.attr('disabled','disabled');
				btnFireupAlarm.attr('disabled','disabled');
				selAlarms.attr('disabled','disabled').html('');
			}
		);
    });		     
	
    $("td[name=mbb4] li").click(function(e){
	   //button awtomobili        
	   switch (e.target.name) {
			case "chbFromRegVisActive":
				if (e.target.checked) {					
					geoLayers[activeRegionId].visibles.carsFromRegion=1;				
					$.each(geoLayers[activeRegionId].carSVG, function(){
						if (this.car_function !=  2 && !this.outLander) this.show();						
					});				
					$(this).find('img').attr('src','img/carsFromRegion.png');					
				} else {
					$.each(geoLayers[activeRegionId].carSVG, function(){
						(this.car_function != 2 && !this.outLander) && this.hide();
					});
					geoLayers[activeRegionId].visibles.carsFromRegion=0;
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/carsFromRegion_grey.png');
				}
				fw.gpsRecalc(geoLayers[activeRegionId],"car");
			break;
			case "chbFromRegVisAll":
				for (var r in geoLayers) {
					if (e.target.checked) {
						geoLayers[r].visibles.carsFromRegion=1;						
						$.each(geoLayers[r].carSVG, function(){ 
							if (this.car_function !=  2 && !this.outLander) this.show();
						});
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/carsFromRegion.png');
					} else {
						$.each(geoLayers[r].carSVG, function(){
							if (this.car_function !=  2 && !this.outLander) this.hide();
						});
						geoLayers[r].visibles.carsFromRegion=0;
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/carsFromRegion_grey.png');
					}
				}	
			break;
			case "chbInRegVisActive":
				if (e.target.checked) {
					geoLayers[activeRegionId].visibles.carsInRegion=1
					$.each(geoLayers[activeRegionId].carSVG, function(){
						if (this.outLander) this.show();						
					});				
//					for (var c in geoLayers[activeRegionId].carSVG) {
//						if (geoLayers[activeRegionId].carSVG[c].mainOffice!=geoLayers[activeRegionId].carSVG[c].idRegion) {							
//							geoLayers[activeRegionId].carSVG[c].show();
//						}
//					}					
					$(this).find('img').attr('src','img/carsInRegion.png');
					fw.gpsRecalc(geoLayers[activeRegionId],"car");
				} else {
					$.each(geoLayers[activeRegionId].carSVG, function(){
						if (this.outLander) this.hide();						
					});
//					for (var c in geoLayers[activeRegionId].carSVG) {
//						if (geoLayers[activeRegionId].carSVG[c].mainOffice!=geoLayers[activeRegionId].carSVG[c].idRegion) {						
//							geoLayers[activeRegionId].carSVG[c].hide();
//						}
//					}
                    geoLayers[activeRegionId].visibles.carsInRegion=0;
					$(e.target).next().attr('checked',false);
					$(this).find('img').attr('src','img/carsInRegion_grey.png');
				}
			break;
			case "chbInRegVisAll":
				for (var r in geoLayers) {
					if (e.target.checked) {
						geoLayers[r].visibles.carsInRegion=1
						$(e.target).prev().attr('checked',true);
						$(this).find('img').attr('src','img/carsInRegion.png');
						$.each(geoLayers[r].carSVG, function(){
							if (this.outLander) this.show();
						});
//						for (var c in geoLayers[r].carSVG) {
//							if (geoLayers[r].carSVG[c].mainOffice!=geoLayers[r].carSVG[c].idRegion) {							
//								geoLayers[r].carSVG[c].show();
//							}
//						}
					} else {
						geoLayers[r].visibles.carsInRegion=0
						$(e.target).prev().attr('checked',false);
						$(this).find('img').attr('src','img/carsInRegion_grey.png');
						$.each(geoLayers[r].carSVG, function(){
							if (this.outLander) this.hide();
						});
//						for (var c in geoLayers[r].carSVG) {
//							if (geoLayers[r].carSVG[c].mainOffice!=geoLayers[r].carSVG[c].idRegion) {						
//								geoLayers[r].carSVG[c].hide();
//							}
//						}
					}
					fw.gpsRecalc(geoLayers[r],"car");
				}				
			break;
	   }
	   fw.autoZoom(activeRegionId);	   
	   
    });
	$("td[name=mbb4] > div.menuBottomButton").mouseenter(function(){        
        $(this).find(".submenu").removeClass('submenu-hide');
    });
    $("td[name=mbb4]").mouseleave(function(){
          $(this).find(".submenu").addClass('submenu-hide');
    });
    
    $("td[name=mbb5] input[name=rbSize]").click(function(){
		sizeLevel = $("input[name=rbSize]:checked").val();
		$.each(geoLayers[activeRegionId].wpSVG,  function(){this.changeSize(sizeLevel);});
		$.each(geoLayers[activeRegionId].carSVG, function(){this.changeSize(sizeLevel);});
		$.each(geoLayers[activeRegionId].objSVG, function(){this.changeSize(sizeLevel);});		
    });
    $("td[name=mbb5] > div.menuBottomButton").mouseenter(function(event){		
        $(this).find(".submenu").removeClass('submenu-hide');	   
    });
    $("td[name=mbb5]").mouseleave(function(event){	    	    
          $(this).find(".submenu").addClass('submenu-hide');		
    });   
	
    $("td[name=mbb6]").click(function(){
	   //button simulaciq
        window.open('simulator.php','_simulator');
		return false;
    });		
		
}
fw.onKey			= function() {
	var regNameSpan = {};
    document.onkeydown = function(e){
		if (dialogOpen) return; 		
		switch (e.keyCode) {
			case 65: // a - alfi										
				if (e.shiftKey) {document.getElementsByName("chbWpVisAll")[0].click();} 
				else			{document.getElementsByName("chbWpVisActive")[0].click();}
				fw.autoZoom(activeRegionId);
			break;
			case 67: //c - cars
				if (e.shiftKey) {document.getElementsByName("chbCarVisAll")[0].click();} 
				else			{document.getElementsByName("chbCarVisActive")[0].click();}
				fw.autoZoom(activeRegionId);
			break;
			case 77: //m - map
				if (e.shiftKey) {document.getElementsByName("chbMapVisAll")[0].click();}
				else			{document.getElementsByName("chbMapVisActive")[0].click();}			
				fw.autoZoom(activeRegionId);
			break;
			case 81: //q - tablet geo
				if (e.shiftKey) {document.getElementsByName("chbTGPSVisAll")[0].click();}
				else			{document.getElementsByName("chbTGPSActive")[0].click();}			
			break;
			case 83: //show region short keys
				$("span.regName").each(function(){
					$(this).css('display','none');
					$(this).next().html($(this).attr("rsk")).css({'color':'grey','margin':'5px'});
				});
			break;
			case 116: //refresh
				e.preventDefault();
				if (confirm("Желаете ли да рестартирате платното?")) {
					$('body').animate({opacity:0},1500,function(){window.location.href="";});				
				}
			break;
			case 70:
				if (e.shiftKey) {document.getElementsByName("chbFromRegVisAll")[0].click();} 
				else			{document.getElementsByName("chbFromRegVisActive")[0].click();}
			break;
			case 73:
				if (e.shiftKey) {document.getElementsByName("chbInRegVisAll")[0].click();} 
				else			{document.getElementsByName("chbInRegVisActive")[0].click();}
			break;
			case 85: //canvas monitring
				(e.shiftKey && e.ctrlKey) && fw.monitoring();
			break;
			case 48:
			case 49:
			case 50:
			case 51:
			case 52:
			case 53:
			case 54:
			case 55:
			case 56:
			case 57:
				var reg = e.keyCode - 48;
				if (e.shiftKey) reg = String(1)+String(reg);
				$("div[key="+reg+"]").click();
			break;
		}	
	};
	document.onkeyup = function(e) {		
		if (e.keyCode==83) {
			$("span.regName").each(function(){
				$(this).css('display','');
				$(this).next().html('')
			});
		}
	}	
}
fw.checkConnection	= function() {
	if (isInit) {
		if (!navigator.onLine) {		
			$("div[name=conStatus]").attr('title','Прекъснат. Натисни за свързване');     
			$("div[name=conStatus] img").attr('src','img/plug-disconnect-slash.png');						
			if (disconnectTimeCounter==5) {
				disconnectTimeCounter=0;				
				sounds.disconnect.play();
			}
			disconnectTimeCounter++;			
			down = true;
		} else {
			(down) && (window.location.href="");			
		}
	}
}
fw.clock			= function(){
	var currentTime = new Date ( );
	var currentHours = currentTime.getHours ( );
	var currentMinutes = currentTime.getMinutes ( );
	var currentSeconds = currentTime.getSeconds ( );
	currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
	currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;
	var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds;
	$("div[name=clock]").html(currentTimeString);
	fw.checkConnection();		
}
fw.gpsDistance		= function(lat1,lat2,lan1,lan2) { 
    var R = 6378137 ; // m
    //var R = 6371; // km
    var d = Math.acos(Math.sin(lan1*Math.PI/180)*Math.sin(lan2*Math.PI/180) + 
            Math.cos(lan1*Math.PI/180)*Math.cos(lan2*Math.PI/180) *
            Math.cos(lat2*Math.PI/180-lat1*Math.PI/180)) * R;    
    return d;
}
fw.gpsRecalc		= function(layer,what) {	
	if(!layer) return;
	var mapLeft		= layer.visibles.map ? bgMap.getBounds().getSouthWest().lng() : layer.zoomBounds.w; 
	var mapTop		= layer.visibles.map ? bgMap.getBounds().getNorthEast().lat() : layer.zoomBounds.n;                         
	var mapRight	= layer.visibles.map ? bgMap.getBounds().getNorthEast().lng() : layer.zoomBounds.e;
	var mapBottom	= layer.visibles.map ? bgMap.getBounds().getSouthWest().lat() : layer.zoomBounds.s;                
	var ratioX	= fw.gpsDistance(0,0,mapLeft,mapRight)/wContW; //oLayers[layer.region]._container.clientWidth;
	var ratioY	= fw.gpsDistance(mapTop,mapBottom,0,0)/wContH; //oLayers[layer.region]._container.clientHeight;   
    
	if (what=="all" || what=="wp")
	$.each(layer.wpSVG,function(k,v){		
		var windY = layer.wpSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = layer.wpSVG[k].geo_lan < mapLeft ? -1 : 1;
		var sX = fw.gpsDistance(0,0,mapLeft,layer.wpSVG[k].geo_lan) / ratioX * windX; 
		var sY = fw.gpsDistance(mapTop,layer.wpSVG[k].geo_lat,0,0) / ratioY * windY;        
		layer.wpSVG[k].move(sX,sY);        		
	});
	if (what=="all" || what=="obj")
	$.each(layer.objSVG,function(k,v){		
		var windY = layer.objSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = layer.objSVG[k].geo_lan < mapLeft ? -1 : 1;	 
		var sX = fw.gpsDistance(0,0,mapLeft,layer.objSVG[k].geo_lan) / ratioX * windX; 
		var sY = fw.gpsDistance(mapTop,layer.objSVG[k].geo_lat,0,0) / ratioY * windY;
		layer.objSVG[k].move(sX,sY);	   	   
		layer.opSVG.move(sX,sY,k);
	});
	if (what=="all" || what=="car")
	$.each(layer.carSVG,function(k,v){			 
		var windY = layer.carSVG[k].geo_lat > mapTop  ? -1 : 1;
		var windX = layer.carSVG[k].geo_lan < mapLeft ? -1 : 1;	 
		var sX = fw.gpsDistance(0,0,mapLeft,layer.carSVG[k].geo_lan) / ratioX * windX; 
		var sY = fw.gpsDistance(mapTop,layer.carSVG[k].geo_lat,0,0) / ratioY * windY;
		var tX = layer.carSVG[k].client_geo_lan == 0 ? 0 : fw.gpsDistance(0,0,mapLeft,layer.carSVG[k].client_geo_lan) / ratioX * windX;
		var tY = layer.carSVG[k].client_geo_lat == 0 ? 0 : fw.gpsDistance(mapTop,layer.carSVG[k].client_geo_lat,0,0) / ratioY * windY;
		
		var aPointsPixel=[];
		for (var i in layer.carSVG[k].aPointsGeo) {
			var windXP = layer.carSVG[k].aPointsGeo[i].geo_lan < mapLeft ? -1 : 1;
			var windYP = layer.carSVG[k].aPointsGeo[i].geo_lat > mapTop  ? -1 : 1;
			var px = fw.gpsDistance(0,0,mapLeft,layer.carSVG[k].aPointsGeo[i].geo_lan) / ratioX * windXP; 
			var py = fw.gpsDistance(mapTop, layer.carSVG[k].aPointsGeo[i].geo_lat,0,0) / ratioY * windYP;
			aPointsPixel.push({px:px,py:py});
		}			
		layer.carSVG[k].move(sX,sY,false,aPointsPixel,tX,tY);		
	});						
}
fw.autoZoom			= function(idReg,withWP) {		
	var west;
	var east;
	var north;
	var south;	
	var ratio = wContW/wContH;
	var one  = 0.000012; //1 metyr
	var minSizeRad = 0.0012;
	var aWE = [];
	var aNS = [];
	
	//obekti
	for (var objId in geoLayers[idReg].objSVG) {
		aWE.push(geoLayers[idReg].objSVG[objId].geo_lan);
		aNS.push(geoLayers[idReg].objSVG[objId].geo_lat);
	}
	//koli
	for (var carId in geoLayers[idReg].carSVG) {	
		if (geoLayers[idReg].carSVG[carId].car_function == 2 && geoLayers[idReg].visibles.cars) {			
			aWE.push(geoLayers[idReg].carSVG[carId].geo_lan);
			aNS.push(geoLayers[idReg].carSVG[carId].geo_lat);
		} else {
			if (geoLayers[idReg].carSVG[carId].car_function == 2 && geoLayers[idReg].carSVG[carId].status!="free") {				
				aWE.push(geoLayers[idReg].carSVG[carId].geo_lan);
				aNS.push(geoLayers[idReg].carSVG[carId].geo_lat);				
			}
		}
		if (geoLayers[idReg].carSVG[carId].aPointsGeo.length) {			
			for(var i=0;i<geoLayers[idReg].carSVG[carId].aPointsGeo.length;i++) {
				aWE.push(geoLayers[idReg].carSVG[carId].aPointsGeo[i].geo_lan);
				aNS.push(geoLayers[idReg].carSVG[carId].aPointsGeo[i].geo_lat);
			}
		}
		if (geoLayers[idReg].visibles.carsFromRegion) {
			if (geoLayers[idReg].carSVG[carId].car_function != 2 && geoLayers[idReg].carSVG[carId].shape.attributes.display.value != 'none') {							
				aWE.push(geoLayers[idReg].carSVG[carId].geo_lan);
				aNS.push(geoLayers[idReg].carSVG[carId].geo_lat);				
			}		
		}
		if (geoLayers[idReg].visibles.carsInRegion && geoLayers[idReg].carSVG[carId].outLander) {					
			aWE.push(geoLayers[idReg].carSVG[carId].geo_lan);
			aNS.push(geoLayers[idReg].carSVG[carId].geo_lat);							
		}
	}	
	//stoqnki
	for (var wpId in geoLayers[idReg].wpSVG) {
		if (geoLayers[idReg].visibles.wp || !geoLayers[idReg].visibles.cars && !geoLayers[idReg].visibles.carsFromRegion && !geoLayers[idReg].visibles.carsInRegion && !geoLayers[idReg].alarm) {						
			aWE.push(geoLayers[idReg].wpSVG[wpId].geo_lan);
			aNS.push(geoLayers[idReg].wpSVG[wpId].geo_lat);
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
		geoLayers[idReg].zoomBounds.w = west;
		geoLayers[idReg].zoomBounds.e = east;
		geoLayers[idReg].zoomBounds.n = north;
		geoLayers[idReg].zoomBounds.s = south;	
	} 
	
	if (geoLayers[idReg].visibles.map) {
//		google.maps.event.addListenerOnce(bgMap,'bounds_changed',function(){ console.log('bounds_changed')});
//		google.maps.event.addListenerOnce(bgMap,'idle',function(){ console.log('idle')});
//		google.maps.event.addListenerOnce(bgMap,'zoom_changed',function(){ console.log('zoom_changed')});
//		google.maps.event.addListenerOnce(bgMap, 'zoom_changed', function(){
//			if (geoLayers[idReg].visibles.wp || !geoLayers[idReg].visibles.cars  && !geoLayers[idReg].alarm) {
//				bgMap.setZoom(bgMap.getZoom()+1);				
//				var mapLeft		= bgMap.getBounds().getSouthWest().lng(); 
//				var mapTop		= bgMap.getBounds().getNorthEast().lat();                          
//				var mapRight	= bgMap.getBounds().getNorthEast().lng(); 
//				var mapBottom	= bgMap.getBounds().getSouthWest().lat();
//				for (var wpId in geoLayers[idReg].wpSVG) {
//					if (geoLayers[idReg].wpSVG[wpId].geo_lan < mapLeft ||
//						geoLayers[idReg].wpSVG[wpId].geo_lan > mapRight ||
//						geoLayers[idReg].wpSVG[wpId].geo_lat > mapTop ||
//						geoLayers[idReg].wpSVG[wpId].geo_lat < mapBottom
//					) {					
//						bgMap.setZoom(bgMap.getZoom()-1);
//						break;
//					}									
//				}				
//			}	
//		});
		bgMap.fitBounds(fw.bgMapMakeBounds());
	}		
	fw.gpsRecalc(geoLayers[idReg],'all');
}
fw.bgMapMakeBounds	= function() {
	var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(geoLayers[activeRegionId].zoomBounds.s,geoLayers[activeRegionId].zoomBounds.w),
					new google.maps.LatLng(geoLayers[activeRegionId].zoomBounds.n,geoLayers[activeRegionId].zoomBounds.e)
	)	
	if (!bounds) return false;
	return bounds;
}
fw.gMaps			= function() {
	var latlng; // = new google.maps.LatLng(43.270456,26.934013);
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
	//google.maps.event.trigger(bgMap,'resize');
}
fw.loadGMScript		= function() {
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=fw.gMaps";
	document.body.appendChild(script);
}
fw.initSVG			= function(svgBox) {
	$(svgBox).svg({settings : {'width':0,'height':0,'preserveAspectRatio':'xMinYMax','viewBox':'0 0 '+wContW+' '+wContH}});
	return $(svgBox).svg('get');
}
fw.activeLayer		= function(regions) {
	var id;
	for (id in regions) {
		if (regions[id].alarm) {
			activeRegionId = id;
			return id;
		}
	}
	activeRegionId = id;
	return id;
}
fw.checkAalarm		= function(region) {
    if (geoLayers[region].alarm && !$("div[name=layer_"+region+"]").parent().hasClass("wcontent")) {		
        if (!geoLayers[region].alarmInterval) {
            geoLayers[region].alarmInterval = setInterval(function(){
					var reaction;
					for (var o in geoLayers[region].objSVG) {
						for (var c in geoLayers[region].carSVG) {
							if (geoLayers[region].carSVG[c].idObject==o && geoLayers[region].carSVG[c].status=="reaction") {				
								reaction = false;								
								break;
							} else {
								reaction = true;				
							}
						}
						if (reaction) break;
					}															
                    $("div[name=layer_"+region+"]").parent().toggleClass(function() {
						if (reaction) {
							$(this).removeClass("unfocusRegionAlarmBorder");
							return "unfocusRegionAlarm";
						} else {
							$(this).removeClass("unfocusRegionAlarm");
							return "unfocusRegionAlarmBorder";
						}
					});
                },500
            );
        }
    } else {
        clearInterval(geoLayers[region].alarmInterval);
        geoLayers[region].alarmInterval=0;
        $("div[name=layer_"+region+"]").parent().removeClass("unfocusRegionAlarm unfocusRegionAlarmBorder");
		$("div[name=selEl_"+region+"]").removeClass("unfocusRegionAlarm unfocusRegionAlarmBorder");
    }        
}
fw.checkNoCars		= function () {
	for (var r in geoLayers) {	
		var patrols = 0;
		var pAll	= 0
		var pReady  = 0;
		$.each(geoLayers[r].carSVG, function(){
			(this.car_function==2) && patrols++;
			(this.car_function==2 && this.idRegion==this.mainOffice) && pAll++;
			(this.statusGeo && this.statusConnection && !this.statusService && this.idRegion==this.mainOffice) && pReady++;			
		});
		(patrols) && ($("div[name=selEl_"+r+"]").removeClass("unfocusRegionNoCars")) || ($("div[name=selEl_"+r+"]").addClass("unfocusRegionNoCars"));
		($("div.wcontent > div[name=layer_"+r+"]")[0]) && $("div.wcontent > div[name=layer_"+r+"] > span > span").html(pAll+"/"+pReady);
		$("div[name=selEl_"+r+"] > div > span > span").html(pAll+"/"+pReady);
		
	}
}
fw.checkRegionCars	= function(r,geo_lat,geo_lan,is_sod) {	
	if (is_sod == 2) return true;
	if (geo_lan >= regionBounds[r].west  && geo_lan <= regionBounds[r].east &&
		geo_lat <= regionBounds[r].north && geo_lat >= regionBounds[r].south) 
	{								
		return true;
	} else {				
		return false;
	}	
}
fw.attachToOperator = function() {	
	for (var o in geoLayers[activeRegionId].objSVG) {
		if (geoLayers[activeRegionId].objSVG[o].isMonitoring && geoLayers[activeRegionId].objSVG[o].statusAlarm) {
			geoLayers[activeRegionId].opSVG.addObject(o);						
			continue;
		} else if (geoLayers[activeRegionId].objSVG[o].isMonitoring && !geoLayers[activeRegionId].objSVG[o].statusAlarm) {
			geoLayers[activeRegionId].opSVG.delObject(o);		
			continue;
		}
		for (var c in geoLayers[activeRegionId].carSVG) {
			if (geoLayers[activeRegionId].carSVG[c].idObject==o || 
				(geoLayers[activeRegionId].carSVG[c].status==="free" && !geoLayers[activeRegionId].carSVG[c].statusService)|| 
				!geoLayers[activeRegionId].objSVG[o].statusAlarm) 
			{				
				geoLayers[activeRegionId].opSVG.delObject(o);
				break;
			} else {
				geoLayers[activeRegionId].opSVG.addObject(o);				
			}
		}		
	}
}
fw.serverCall		= function(type,data,success,context,error) {
	jQuery.ajax({
		type:'POST',
		async:true,
		url:'canvas_api.php',
		data:{
			request:JSON.stringify({
				type:type,
				data:data
			})
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
fw.sendCommand		= function(command_type,data,success,error,sCmd) {	
	var cmd = sCmd || "command";
	fw.serverCall(
		cmd,
		{command_type:command_type,data:data},
		success,
		error
	);
}
fw.saveDispatcherFactor = function(pkg) {
	var data = {id_person:ID_PERSON,id_alarm_register:pkg.idAlarmRegister,id_region:pkg.idRegion};
       
	fw.serverCall('dispatcher_factor', data, function(resp){
		//console.log(resp);
	});
}

fw.alarmNotifications = function() {
	fw.serverCall("notifications",{},function(resp){
		
		var i;
		
		/* NOTIFICATIONS */
		$('.tblNotifications').empty();
		var notifications = resp.notifications;
		console.log(notifications);
		if (notifications.length) {
			var html = '';
			for (i in notifications) {
				html += '<li class="notification_row" id_notification="'+notifications[i]['id']+'"><img style="height:11px;" src="img/hiks_over.png" /> Телефон: <a href="sip:'+notifications[i].target+'">' +
								notifications[i].target + 
							'</a> ' +
						notifications[i].additional_params.signal_time+' ['+notifications[i].object_num+'] '+notifications[i].object_name + " " +
						notifications[i].additional_params.signal_name +
						'</li>';
			}
			$('.tblNotifications').append(html);
		}
		$('#notifications_count').html(notifications.length);
		/* END OF NOTIFICATIONS */
		
		/* ALARM PANICS */
		
		$('.tblAlarmPanics').empty();
		var alarm_panics = resp.alarm_panics;
		
		if(alarm_panics.length) {
			html = '';
			for(i in alarm_panics) {
				html += '<li class="alarm_panic_row" id_alarm_panic="'+alarm_panics[i].id+'"><img style="height:11px;" src="img/hiks_over.png" /> '+alarm_panics[i].alarm_time + ' '+alarm_panics[i].object_name+'</li>'
			}
			$('.tblAlarmPanics').html(html);
		}
		
		$('#alarm_panics_count').html(alarm_panics.length);
		/* END OF ALARM PANICS */
		
	});	
}
fw.clickBtnNotifications = function(jel) {
	var notifications_table = $('.tblNotifications');
	
	if(notifications_table.css('display') != 'none') {
		notifications_table.css('display','none');
		$("#btnNotifications >img").attr("src","../images/add_more.gif");
		$('#divNotifications').animate({height:20,width:170}, 300);
	} else {
		notifications_table.css('display','');
		$("#btnNotifications > img").attr("src","../images/delete_more.gif");
		$('#divNotifications').animate({height:120,width:600}, 300);
	}
}

fw.clickBtnAlarmPanics = function(jel) {
	var alarm_panics_table = $('.tblAlarmPanics');
	
	if(alarm_panics_table.css('display') != 'none') {
		alarm_panics_table.css('display','none');
		$("#btnAlarmPanics >img").attr("src","../images/add_more.gif");
		$('#divAlarmPanics').animate({height:20,width:210}, 300);
	} else {
		alarm_panics_table.css('display','');
		$("#btnAlarmPanics > img").attr("src","../images/delete_more.gif");
		$('#divAlarmPanics').animate({height:120,width:600}, 300);
	}
}


fw.alarmArchive		= function() {
	$("#divArchive").draggable({containment: $('#activeRegion'), axis: 'y'});
	$("#imgArchive").bind('click',function(event,id_obj) {	
		archiveOpen = !archiveOpen;		
		if (archiveOpen) {
			var _bgcolor	= "";							
			var _this		= $(this).parent().parent();
			var _width		= 100;
			var _height		= 30;			
			var _obj_name	= id_obj ? geoLayers[activeRegionId].objSVG[id_obj].name : "";
			$(this).attr("src","img/ui-anim_basic_16x16.gif");			
			fw.sendCommand("archive",{idRegion:activeRegionId,idPerson:ID_PERSON,idObject:id_obj || 0},function(resp){				
				if (resp.length) {															
					for (var i in resp) {
						 (resp[i].alarm_status==1) && (_bgcolor="rgba(240,230,140,0.5)") || (resp[i].alarm_status==2) && (_bgcolor="rgba(255,192,203,0.5)") || (_bgcolor="");						 
						 var li = document.createElement("li");				 
						 var btnPlay = document.createElement("image");
						 $(btnPlay).attr({src:'img/play_grey.png',width:'16',height:'16',idar:resp[i].id_alarm_register || ""})
								   .css({'padding-right':'3px','vertical-align':'middle'})
								   .click(function(){if ($(this).attr('idar')) window.open('player.php?id_alarm='+$(this).attr('idar'),'_player'); else fw.alert("Системно съобщение", "Няма запис за събитието.");})
								   .mouseenter(function(){$(this).attr('src','img/play.png');})
								   .mouseout(function(){$(this).attr('src','img/play_grey.png');});						   
						 $(li).append(btnPlay)				 
							  .append(resp[i].alarm_time+"&nbsp;["+resp[i].obj_num+"]&nbsp;"+(resp[i].obj_name || _obj_name)+"&nbsp;"+resp[i].alarm_name+"&nbsp;")
							  .css({'list-style-type':'none','padding-bottom':'3px','white-space':'nowrap','cursor':'pointer','background-color':_bgcolor})
							  .attr({'idar':resp[i].id_alarm_register || "",oname:resp[i].obj_name,onum:resp[i].obj_num,oalarm:resp[i].alarm_name,for_bulletin:resp[i].for_bulletin})
							  .click(function(evt){
								  
								  dialogOpen = true;
								  if (evt.target.tagName==="IMG") return;
								  var idar = $(this).attr('idar') || "";								  
								  var for_bulletin = $(this).attr('for_bulletin') || "";								  
								  if (!idar) {
									  fw.alert("Систмено съобщение", "Не може да добавите коментар за това събитие");
									  return;
								  }
								  var dContent = "["+$(this).attr('onum')+"] "+$(this).attr('oname')+" "+$(this).attr('oalarm') + 
												 "<textarea name='txtComment' style='width:98%; height:100px;'></textarea>";	

								var checked = for_bulletin == '1' ? 'checked' : '';

								dContent += '<input type="checkbox" name="for_bulletin" '+checked+' />Алармата като Бюлетин в www.com';

								  fw.confirm('Добавяне на коментар', dContent,'',function(){
										var comment=$("textarea[name=txtComment]").val();
										var for_bulletin=$("input[name=for_bulletin]").is(':checked');
										if(for_bulletin) {
											$('li[idar='+idar+']').attr('for_bulletin','1');
										} else {
											$('li[idar='+idar+']').attr('for_bulletin','0');
										}
										
										fw.sendCommand("write_comment",{id_person:ID_PERSON,comment:comment,id_alarm_register:idar,for_bulletin:for_bulletin},
										function(resp){ },
										function(resp){},"write_comment");
										dialogOpen = false;
								  },function(){dialogOpen = false;});
							  });									  
						 if (resp[i].comments) {
							 var aComments = resp[i].comments.split("::");
							 var comment = "";
							 for (var c in aComments) {
								 comment += aComments[c].split("@@")[0]+"\n";
								 comment += aComments[c].split("@@")[1]+"\n";
								 comment += aComments[c].split("@@")[2]+"\n";
								 comment += "--------------------\n"
							 } 	
							$(li).attr('title',comment); 					 
						 } 
						 $(_this).find(".tblArchive").append(li);				 
					}
					$(_this).find(".tblArchive li").each(function(i,el){			
						($(el).width()>_width) && (_width=$(el).width());
					 });										
					$(_this).animate({height:120,width:_width+20}, 300);
					$("#btnArchive > img").attr("src","../images/delete_more.gif");
				} else {
					$("#btnArchive >img").attr("src","../images/add_more.gif");
				}
			},function(resp){console.log(resp)},"archive");					
		} else {
			$(this).parent().parent().find(".tblArchive").html('');
			$(this).parent().parent().animate({height:'20px',width:'70px'}, 300);
			$(this).attr("src","../images/add_more.gif");
		}
	});
}
fw.serviceBypass	=  function() {	
	$("#divServiceBypass").draggable({containment: $('#activeRegion'), axis: 'y'}).css({top:$('#activeRegion').height()});
	$("#imgServiceBypass").click(function() {
		serviceBypassOpen = !serviceBypassOpen;
		if (serviceBypassOpen) {
			var _this  = $(this).parent().parent();
			var _width = 135;
			var _height = 20;
			var sCount = 0;
			var bCount = 0;
			$("#btnServiceBypass >img").attr("src","img/ui-anim_basic_16x16.gif");	
			fw.sendCommand("get_service_bypass",{idRegion:activeRegionId,idPerson:ID_PERSON},function(resp){
				if (resp.length) {
					for (var i in resp) {						
						(resp[i].type=='bypass') && (++bCount) || (++sCount);
						var li = document.createElement("div");												
						$('<div></div>').addClass(resp[i].type=='bypass'?'btnRemoveBypass':'btnRemoveService')
										.text(resp[i].type=='bypass'?'Б':'С')
										.attr({oid:resp[i].obj_id,
												obj_type:	resp[i].type,
												id_bypass:	resp[i].id_bypass,
												obj_num:	resp[i].obj_num,
												obj_name:	resp[i].obj_name
										})
										.click(function(e){
											var that = $(this);
											fw.confirm("["+$(this).attr('obj_num')+"]&nbsp;"+$(this).attr('obj_name') ,
													   "Режим "+($(this).attr('obj_type')=="bypass" ? "байпас" : "сервизен")+" ще бъде прекратен.",
													   'caution',
													   function() {
															var _data = {id_object:that.attr('oid'),obj_type:that.attr('obj_type'),id_bypass:that.attr('id_bypass')};															
															if (that.attr('obj_type')=="service") {
																fw.sendCommand('stop_service_status',_data,function(res){
																	$(e.target).parent().remove();
																	var aWidth = [];
																	$(_this).find(".tblServiceBypass div").each(function(i,el){aWidth[i] = $(el).width();});																
																	$(_this).animate({height:120,width:Math.max.apply(Math, aWidth)+20}, 300);
																},function(res){},'stop_service_status');
															} else {
																fw.sendCommand('remove_bypass_alarm',_data,function(res){ 
																	$(e.target).parent().remove(); 
																	var aWidth = [];
																	$(_this).find(".tblServiceBypass div").each(function(i,el){aWidth[i] = $(el).width();});																
																	$(_this).animate({height:120,width:Math.max.apply(Math, aWidth)+20}, 300);
																});
															}																
													    }
											);
										})
										.appendTo(li);
						$(li).append("["+resp[i].obj_num+"]&nbsp;"+resp[i].obj_name+"&nbsp;"+resp[i].alarm_name+"&nbsp;")
							  .css({'padding-bottom':'3px','white-space':'nowrap','cursor':'pointer'})
							  .attr({oname:resp[i].obj_name,onum:resp[i].obj_num,oalarm:resp[i].alarm_name});
						$(_this).find(".tblServiceBypass").append(li);						
					}
					$(_this).find(".tblServiceBypass div").each(function(i,el){			
						($(el).width()>_width) && (_width=$(el).width());
					 });										
					$(_this).animate({top:$("#divServiceBypass").offset().top - 100 + "px",height:120,width:_width+20}, 300, function(){
						
					});
					$("#btnServiceBypass > img").attr("src","../images/delete_more.gif");
					$("#nService").text("("+sCount+")");
					$("#nBypass").text("("+bCount+")");
				} else {
					$("#btnServiceBypass > img").attr("src","../images/add_more.gif");
				}				
			},function(resp){  },"get_service_bypass");			
		} else {
			
			$(this).parent().parent().find(".tblServiceBypass").html('');
			$(this).parent().parent().animate({top:$("#divServiceBypass").offset().top + 100 + "px",height:'20px',width:'135px'}, 300);
			$(this).attr("src","../images/add_more.gif");
			$("#nService").text("");
			$("#nBypass").text("");
		}
	});
}
fw.confirm			= function(title,msg,type,confirm,cancel) {
	var icon = "";
	switch (type) {
		case 'caution':
			icon = "<img src='../images/"+type+".gif' style='vertical-align: middle;margin-right:10px;float:left;' />";
		break;		
	}
	$('<div></div>').attr('title',title)
					.css({'width':'300px','font-family':'Verdana,Arial,sans-serif','display':'table-cell'})
					.append(icon)
					.append(msg)
					.dialog({
						buttons:{
							"Потвърди" : function() {
								(typeof confirm === "function") && confirm();
								$(this).dialog("close");
								$(this).remove();
							},
							"Отмени" : function() {
								(typeof cancel === "function") && cancel();
								$(this).dialog("close")
								$(this).remove();
							}
						},
						modal: true,
						dialogClass: 'confirmDialog'						
					});

}
fw.alert			= function(title,msg){
	$('<div></div>').attr('title',title)
					.css({'width':'300px','font-family':'Verdana,Arial,sans-serif','display':'table-cell'})
					.append(msg)
					.dialog({
						buttons:{
							"Затвори" : function() {								
								$(this).dialog("close");
								$(this).remove();
							}
						},
						modal: true,
						dialogClass: 'confirmDialog'						
					});

}
fw.suggest			= function(elInput,elValue,onSelect,onChange) {
	elInput.autocomplete({
		source: function( request, response ) {	
				setTimeout(function(){
					fw.sendCommand('suggest_object', {idRegion: activeRegionId, startsWith: request.term},
						function(resp){
							response($.map(resp, function(item) {
								return {
									label: item.name,
									value: item.value,
									num: item.num
								}
							}));
						},
						function(resp){},
						'suggest_object');
				},2000);
			
			},			
			select: function(event, ui) {
				elInput.val(ui.item.label);
				for (var i in ui.item) {
					if (!ui.item.hasOwnProperty(i)) continue;
					elValue.attr(i,ui.item[i])
				}											
				(typeof onSelect === "function") && onSelect();
				return false
			},
			change: function(event, ui) {			
				if(!ui.item) {
					elValue.val('');
					(typeof onChange === "function") && onChange();
				}
			}			
	});
}
fw.bunchSelector	= function(e) {		
	var wind = e.offsetY < 100 ? 55 : -100;	
	var bunchSelector = $("#bunchSelector");
	if (bSelector) {
		$('#bunchSelectorTbl').remove();
		$("#activeRegion").unmousewheel();	
		bunchSelector.unbind();
	}	
	bSelector = true;			
	var collector = [];
	var lenCollector = 0;
	var current = 0;	
	
	var inRange = function(o,r) {		
		var pos = Math.pow((e.offsetX - o.newX),2) + Math.pow((e.offsetY - o.newY),2);		
		return pos < r*r;		
	}			
	bunchSelector.css({top:	e.clientY - 50, left:	e.clientX - 50});	
	
	for (var cr in geoLayers[activeRegionId].carSVG) {		
		if (inRange(geoLayers[activeRegionId].carSVG[cr],50)) collector.push({elRef:geoLayers[activeRegionId].carSVG[cr]});		
	}
	for (var wp in geoLayers[activeRegionId].wpSVG) {		
		if (inRange(geoLayers[activeRegionId].wpSVG[wp],50)) collector.push({elRef:geoLayers[activeRegionId].wpSVG[wp]});		
	}
	for (var ob in geoLayers[activeRegionId].objSVG) {		
		if (inRange(geoLayers[activeRegionId].objSVG[ob],50)) collector.push({elRef:geoLayers[activeRegionId].objSVG[ob]});		
	}
	lenCollector = collector.length;
	var tbl = $('<div></div>')
				.css({position:'absolute','z-index':'1100'})
				.attr('id','bunchSelectorTbl');
	var bunchHeader = $('<div></div>')
				.addClass('bunchHeader')				
				.attr('id','bunchHeader')
				.appendTo(tbl);
	
	for (var i=0;i<lenCollector;i++) {		
		var el = $('<div></div>')
		  .addClass('bunchUnselected')		  		  
		  .append("<img width='16' height='16' style='margin:2px;' src='"+collector[i].elRef.getImage()+"'></img>")
		  .appendTo(tbl)
		collector[i].bunchRef = el;				
	}			
	collector[0].bunchRef.addClass('bunchSelected');	
	bunchHeader.html(collector[0].elRef.getHeader());		
	tbl.css({left:e.clientX - lenCollector * 12,top:e.clientY + wind}).appendTo($('#activeRegion'));
	bunchSelector.show('explode',{},'fast');	
	
	$('#activeRegion').mousewheel(function(event, delta, deltaX, deltaY) {		
		if (delta > 0 && current < lenCollector) {	
			if(current < lenCollector-1) {
				collector[current].bunchRef.toggleClass('bunchSelected');
				current++;
				(collector[current]) && collector[current].bunchRef.toggleClass('bunchSelected');
			}			
		} else if(delta < 0 && current>0) {
			(current == lenCollector) && current--;
			if(current > 0) {
				collector[current].bunchRef.toggleClass('bunchSelected');
				current--;
				collector[current].bunchRef.toggleClass('bunchSelected');
			}
		}	
		bunchHeader.html(collector[current].elRef.getHeader());
		return false;
		event.preventDefault();
	});
	var clickSelected = function() {collector[current].elRef.doClick();}
	bunchSelector.mouseup(function(e){
		
		$(this).hide('explode',{},'fast',function(){if (e.button==0) clickSelected();});
		$('#bunchSelectorTbl').remove();
		$('#activeRegion').unmousewheel();
		$(this).unbind();
		bSelector = false;						
	});	
}
fw.countBytesRcv	= function(len) {	
	var _time =  Math.round(new Date().getTime() / 1000);
	if (_time == sockBytesReceived[59].ts) {
		sockBytesReceived[59].len += len;
	} else {
		sockBytesReceived.push({len : len, ts : _time});
		sockBytesReceived.shift();	
	}
}
fw.extend			= function(subClass, baseClass) {
   function inheritance() {}
   inheritance.prototype = baseClass.prototype;
   subClass.prototype = new inheritance();
   subClass.prototype.constructor = subClass;
   subClass.baseConstructor = baseClass;
   subClass.superClass = baseClass.prototype;
}

//BAZOV KLAS NA SVG FORMITE
fw.baseShape                      = function(x,y,shape) {
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
fw.baseShape.prototype.getNewX    = function()     {return this.newX}
fw.baseShape.prototype.getNewY    = function()     {return this.newY}
fw.baseShape.prototype.hide       = function()     {
	if (this instanceof fw.carShape && this.car_function == 2) {
		$(this.shape).find("*").each(function(index,child){
			if ($(child).attr('carTablet') || $(child).attr('rectNumCarTablet') || $(child).attr('txtNumCarTablet')) return true;
			$(child).attr('display','none');
		});
	} else {
		$(this.shape).attr('display','none');
	}
}
fw.baseShape.prototype.show       = function()     {
	if (this instanceof fw.carShape && this.car_function == 2) {
		$(this.shape).find("*").each(function(index,child){
			if ($(child).attr('carTablet') || 
				$(child).attr('rectNumCarTablet') || 
				$(child).attr('txtNumCarTablet') ||
				$(child).attr('line') ||
			    $(child).attr('shadowStart')||
				$(child).attr('gTimer')) return true;
			$(child).attr('display','');
		});
	}
//	} else {
		$(this.shape).attr('display','');
//	}
}
fw.baseShape.prototype.goLast     = function()     {
    (!$(this.shape).is(":last-child")) && ($(this.shape).insertAfter(activeSVG.find("svg > *:last-child")));
}
fw.baseShape.prototype.calcOffset = function(width,height,parentD,hDirection,wSubmenu) {   
    var right = hDirection || false;    
    var xx = right ? this.getNewX() + parentD + 10  : this.getNewX() - width - sizeLevel;    
    var yy = this.getNewY() - parentD/2;   
    (!right) && (xx + wSubmenu < 0) && (xx = this.getNewX() + parentD) && (right = true);
    (right)  && (xx + width + wSubmenu > $('.wcontent').width()) && (xx = this.getNewX() - width - sizeLevel) && (right = false);
    (yy + height > $('.wcontent').height()) && (yy = $('.wcontent').height() - height - 1);       
    return {x:xx,y:yy,right:right};
}
fw.baseShape.prototype.createMenu = function(menu) { 	
    //clean
    if (this.menuList) {	    	    
        //$(menu.params.owner).unbind('click');       
        for (var i=0;i<this.aMenus.length;i++) {
            $(this.aMenus[i]).remove();
            $(this.aTexts[i]).remove();               
        }        
	  
        if(this.aSubmenus && this.aSubmenus.length) {			   
		   for (var i=0;i<this.aSubmenus.length;i++) {
			  $(this.submenuList[i]).remove();
			  for (var sm in this.aSubmenus[i]) {
				$(this.aSubmenus[i][sm]).remove();
				$(this.aSubtexts[i][sm]).remove();  
			  }			  
		   }
	   }
        $(this.menuList).remove();             
        $(this.menuListLine).remove();
    }
    //construct    
    this.menuSize = menu.list.length;        
    this.aMenus = [];
    this.aTexts = [];
    this.menuList = {};
    this.aSubmenus = [];
    this.aSubtexts = [];   
    this.submenuTimeout = {};
    this.submenuList = {};

    var that = this;
    var _gsm = {};
    
    this.menuList = menu.params.svg.rect(menu.params.gMenu,0,0,150,this.menuSize*20,5,5,{'class':'listMenu'});        
    for (var key=0;key<menu.list.length;key++) {   
        that.aTexts[key] = menu.params.svg.text(menu.params.gMenu, 0,0,menu.list[key].txt,{style:'font-size:12px;',menuPart:1});
        that.aMenus[key] = menu.params.svg.rect(menu.params.gMenu,0,0,148,20,5,5,{'class':'objectMenu',key:key,menuPart:1});        
       
		var smSize;
		if (menu.list[key].hasOwnProperty("submenu")) {
			that.aSubtexts[key]={};
			that.aSubmenus[key]={};
            smSize = menu.list[key].submenu.length;		
            _gsm[key] = menu.params.svg.group(menu.params.gMenu,{name:"submenu",display:"none",key:key});            
            that.submenuList[key] = menu.params.svg.rect(_gsm[key],0,0,170,smSize*20,5,5,{'class':'listMenu'});

			for(var k=0;k<menu.list[key].submenu.length;k++) {					
                that.aSubtexts[key][k] = menu.params.svg.text(_gsm[key], 0,0,menu.list[key].submenu[k].txt,{style:'font-size:12px;'});
                that.aSubmenus[key][k] = menu.params.svg.rect(_gsm[key],0,0,168,20,5,5,{'class':'objectMenu',pKey:key,key:k});
                $(that.aSubmenus[key][k]).click(function(){				
                    (menu.list[$(this).attr('pKey')].submenu[$(this).attr('key')].action) && 
					(menu.list[$(this).attr('pKey')].submenu[$(this).attr('key')].action());
                    clearTimeout(that.submenuTimeout);
                    $(_gsm[$(this).attr('pKey')]).attr('display','none');
                    $(menu.params.gMenu).attr('display','none');
                });
                $(that.aSubmenus[key][k])
                    .mouseenter(function(){clearTimeout(that.submenuTimeout);})
                    .mouseleave(function(){
						var smKey = this;
                        that.submenuTimeout = setTimeout(function(){$(_gsm[$(smKey).attr('pKey')]).attr('display','none');},300);				    
                });
            };                                    
        }
	   
        $(this.aMenus[key]).mouseenter(function(){
			var subSize = menu.list[$(this).attr('key')].submenu.length;
            var co = that.calcOffset(150,subSize*20,sizeLevel,true,170); //coordinates                
            //var objShift = that instanceof fw.objectShape ? sizeLevel/-1.7 : 0;		  
			var objShift = co.right ? 0 : -20; 
            var wind = co.right ? 1 : -1;               
			var py = parseInt($(this).attr('y'));
			(py + subSize*20 > $('.wcontent').height()) && (py = co.y);
            $(that.submenuList[$(this).attr('key')]).attr('x',co.x+150*wind+objShift).attr('y',py);		  		  
            for (var c=0;c<subSize;c++) {			  
                $(that.aSubtexts[$(this).attr('key')][c]).attr('x',co.x+150*wind+10+objShift).attr('y',py+15+c*20);
                $(that.aSubmenus[$(this).attr('key')][c]).attr('x',co.x+150*wind+1+objShift).attr('y',py+c*20);
            }                		  		  
            $(_gsm[$(this).attr('key')]).attr('display','');
        }).mouseleave(function(){
		  var oaMenus = this;
            that.submenuTimeout = setTimeout(function(){
			  $(_gsm[$(oaMenus).attr('key')]).attr('display','none');
		  },100)            
        });   
        $(that.aMenus[key]).click(function(){		  
            (menu.list[$(this).attr('key')].action) && menu.list[$(this).attr('key')].action();
            //$(menu.params.gMenu).attr('display','none');
        });
    };
	
    this.menuListLine = menu.params.svg.line(menu.params.gMenu,0,0,0,0,{strokeWidth: 1, stroke: "black",'opacity': "0.5",'display':''});
    var _line = this.menuListLine;    
    var co = that.calcOffset(150,that.menuSize*20,sizeLevel,true,170); //coordinates   
   // var objShift = that instanceof fw.objectShape ? sizeLevel : 0;
    var objShift=0;	
    that.goLast();                     
    $(that.menuList).attr('x',co.x+objShift).attr('y',co.y+objShift);
    for (var i=0;i<that.menuSize;i++) {
	  $(that.aTexts[i]).attr('x',co.x+(co.right?10:10)+objShift).attr('y',co.y+15+i*20+objShift);
	  $(that.aMenus[i]).attr('x',co.x+1+objShift).attr('y',co.y+i*20+objShift);
    }     
	
    $(_line).attr("x1",co.x+(!co.right?150:0)+objShift).attr("y1",co.y+10+objShift).attr("x2",that.getNewX()+objShift).attr("y2",that.getNewY()+objShift);        
}
fw.baseShape.prototype.destroyMenu= function() {
	if (this.menuList) {	    	              
		for (var i=0;i<this.aMenus.length;i++) {
			$(this.aMenus[i]).remove();
			$(this.aTexts[i]).remove();               
		}        	  
		if(this.aSubmenus && this.aSubmenus.length) {			   
			for (var i=0;i<this.aSubmenus.length;i++) {
				$(this.submenuList[i]).remove();
				for (var sm in this.aSubmenus[i]) {
					$(this.aSubmenus[i][sm]).remove();
					$(this.aSubtexts[i][sm]).remove();  
				}			  
			}
		}
		$(this.menuList).remove();             
		$(this.menuListLine).remove();		      
		this.aMenus = [];
		this.aTexts = [];
		this.menuList = {};
		this.aSubmenus = [];
		this.aSubtexts = [];   
		this.submenuTimeout = {};
		this.submenuList = {};
    }
}

//OBEKTI
fw.objectShape	 = function(svg,name,x,y,draw) {    	
	var txt 
	var rectNum;
	var txtNum;
	var txtTimer;
	var gTimer;	
	var gSubmenu;
	var gReason;
	var reasonRect;
	var reasonText;
	var circle;
	var range;
	var reactionTimeInterval;
	var reasonTimeOut;
	var that = this;		
	var _g = svg.group({name:name,display:'none'});
	fw.objectShape.baseConstructor.call(this,x,y,_g);
	
	this.id_zone;    
	this.address;
	this.info;
	this.status; //alarm;service;cancel
	this.message = {time:0,msg:''};
	this.reason;
	this.time = 0;
	this.obj_time_alarm_reaction = 0;
	this.event_type;
	this.mainSig;
	this.statusAlarm;
	this.statusServise;
	this.gMenu;
	this.menuIsShown = false;
	this.attachedCars = {};
	this.alarmElapsedTime;
	this.isMonitoring;
	this.geo_lat;
    this.geo_lan;	
	this.elType="object";
			
	this.create = function() {
		if (circle) return;        
		gTimer	= svg.group(_g,{svgX:this.X-15,svgY:this.Y+15,strokeWidth:1,name:name+'_timer',display:'none;'});        
		gReason	= svg.group(_g,{name:name+"_reason",display:'none'});
		this.gMenu = svg.group(_g,{name:name+"_menu",display:"none"});
		gSubmenu	= svg.group(_g,{name:name+"_submenu",display:"none"});
		rectNum = svg.rect(_g,0,0,0,20,5,5,{'class':'bgRect'});
		txtNum = svg.text(_g,0,0,"",{fill:'black',style:'text-anchor:middle;font-size:10px;',name:name+"_txtnum"});
		//range  = svg.circle( _g,this.X,this.Y,sizeLevel*2,{'fill-opacity': "0.1",'display':'none','fill': 'blue'});    
		circle = svg.circle( _g,this.X,this.Y,sizeLevel/2,{'fill-opacity': "0.5",'stroke-opacity': "0.5",'fill': 'white','stroke': 'blue','strokeWidth': 1});               
		txtTimer = svg.text(gTimer,0,0,'0:00',{fill:'green',style:'font-size:10px;',name:name+"_txttimer"});         
		reasonRect = svg.rect(gReason,0,0,148,20,5,5,{'class':'bgRect'});
		reasonText = svg.text(gReason,0,0,'',{'class':'reasonText',name:name+"_txtreason"}); 
		$(circle).bind('contextmenu',function(e){fw.bunchSelector(e);});		
		$(circle).click(function(e){
			if (archiveOpen) {				
				$("#imgArchive").click();
				$("#imgArchive").trigger('click',[that.id]);
				return;
			}			
			if(!that.menuIsShown) {
				that.menuIsShown = true;
				that.showMenu();			
				that.goLast();
				jQuery(document.body).bind('click.car-menu',function(){
					that.menuIsShown = false;
					that.destroyMenu();
					jQuery(document.body).unbind('click.car-menu');
				});
				e.stopPropagation();
			}
		});	
		$(circle).mouseenter(function(){that.goLast();});
	}	 
	this.showMenu = function() {		
		if (!that.menuIsShown) {
			$(that.gMenu).attr('display','none');
			this.destroyMenu();
			return;
		}
		var mainMenu;
		var smReaction = [];		
		var smFree = [];
		var smBypass = [];
		var smReasons = [];
		var smMonitoring = [];
		for (var car in geoLayers[that.idRegion].carSVG) {
			if (geoLayers[that.idRegion].carSVG[car].idObject!=that.id && !geoLayers[that.idRegion].carSVG[car].nopatrol) {
				smReaction.push({
					txt : geoLayers[that.idRegion].carSVG[car].callsign,
					idCar : car,
					action:function() {fw.sendCommand('car_announce',{id_object:that.id,id_car:this.idCar})}
				});		
			} else if(geoLayers[that.idRegion].carSVG[car].idObject==that.id && !geoLayers[that.idRegion].carSVG[car].nopatrol) {
				smFree.push({
					txt : geoLayers[that.idRegion].carSVG[car].callsign,
					idCar : car,
					action:function() {fw.sendCommand('car_reject',{id_car:this.idCar})}
				});
			}
		}
		for (var al in alarmReasonsBypassObject) {					
			smBypass.push({txt : alarmReasonsBypassObject[al],
						idAlarm : al,
						action: function(){fw.sendCommand('bypass_alarm',{id_object:that.id,id_reason:this.idAlarm})}
			});
		}
		
		for (var alm in alarmReasonsObjectMonitoring) {					
			smMonitoring.push({txt : alarmReasonsObjectMonitoring[alm],
						idAlarm : alm,
						action: function(){fw.sendCommand('alarm_notify',{id_object:that.id,id_reason:this.idAlarm})}
			});
		}
		for (var alr in alarmReasonsObject) {					
			smReasons.push({txt : alarmReasonsObject[alr],
						idAlarm : alr,
						action: function(){fw.sendCommand('alarm_reason',{id_object:that.id,id_reason:this.idAlarm})}
			});
		}
		mainMenu = [
			{txt:'Анонс към',submenu:smReaction},
			{txt:'Освободи',submenu:smFree},
			{txt:"Байпас",submenu:smBypass},			
			{txt:"Информация",action:function(){
					fw.sendCommand("objinfo",{id_object:that.id,num_object:that.num,id_alarm_register:that.idAlarmRegister},function(resp){							
						var tbl = document.createElement("table");			
						var tbla = document.createElement("table");
						$(tbl).attr({
							border:1,
							cellpadding:3,
							cellspacing:1,
							width:'100%'
						}).css({
							borderCollapse:'collapse'
						});
						var aMol = resp.info[0].mol.split('@');
						var sStatus = resp.info[0].id_status != 1 ? 'style="background-color: #f33737; color:white;"' : 'style="background-color: #25d336; color:black;"';
						var sCorporate = resp.info[0].id_office == 72 ? ' | КОРПОРАТИВНИ КЛИЕНТИ ' : ''; 
						$(tbl).append("<tr><th>Статус</th><td " + sStatus + ">"+resp.info[0].status + sCorporate + "</td></tr>")
							  .append("<tr><th>Име</th><td><span name='objInfo' oid='"+resp.info[0].id+"' style='cursor:pointer; color:blue;'>"+resp.info[0].name+"</span></td></tr>")
							  .append("<tr><th>Адрес</th><td>"+resp.info[0].address+"  "+resp.info[0]._address_other+"</td></tr>")
							  .append("<tr><th>Местонахождение</th><td>"+resp.info[0].place+"</td></tr>")
							  .append("<tr><th>Информация</th><td>"+resp.info[0].operativ_info+"</td></tr>")
							  .append("<tr><th>МОЛ</th><td>"+resp.info[0].mol.split('@').join('</br>')+"</td></tr>")
							  .append("<tr><td colspan='2' align='center'><button name='setObjectGeo'>Установи координати на обкет "+that.num+" за автопатрула</button></td></tr>");
						//$(tbl).find("td").attr("nowrap","nowrap")
						$(tbl).find("span[name=objInfo]").click(function(){
							window.open(BASE_URL+"/telenet/page.php?page=object_info&nID="+$(this).attr('oid'),'_blank','width=1080,height=515,resizable=no');
						});
						$(tbl).find("button[name=setObjectGeo]").click(function(){
							var attachedCar;
							$.each(geoLayers[that.idRegion].carSVG,function(k,v){
								if (v.idObject == that.id) { 
									attachedCar=v.id;
									return false;
								}
							});	
							if (attachedCar) {								
								fw.confirm( "Системно съобщение", 
											"Координатите на обекта ще бъдат сменени с тези на реагиращия патрул!", 
											"caution",
											function(){
												var geo_lat = geoLayers[that.idRegion].carSVG[attachedCar].geo_lat;
												var geo_lan = geoLayers[that.idRegion].carSVG[attachedCar].geo_lan;
												fw.sendCommand("obj_set_geo",{id:that.id,geo_lat:geo_lat,geo_lan:geo_lan},
													function(resp){									
														that.isMonitoring = false;
														fw.attachToOperator();
														that.setStatus();
														that.geo_lat = geo_lat;
														that.geo_lan = geo_lan;
														fw.gpsRecalc(geoLayers[that.idRegion], "all");	
													},
													function(resp){console.log('resp');},
													"obj_set_geo"
												)																		
											}
								);								
							} else {
								fw.alert("Системно съобщение", "Няма реагиращ патрул за този обект");
							}
						});
						$(tbla).attr({
							border:1,
							cellpadding:3,
							cellspacing:1,
							width:'100%'
						}).css({
							borderCollapse:'collapse',
							marginTop:'5px'
						});
						$(tbla).append("<tr><th colspan='2'>Последни 10 сигнала</th></tr>");						
						$.each(resp.alarms,function(k,v){
							var _bgcolor="";
							(v.alarm_status==1) && (_bgcolor="khaki") || (v.alarm_status==2) && (_bgcolor="pink");							
							$(tbla).append("<tr style='background-color:"+_bgcolor+"'><td>"+(v.time)+"</td><td><span class='to_archive' oid='"+resp.info[0].id+"' style='cursor:pointer; color:blue;'>"+v.msg+"</span></td></tr>");
						});
						$(tbla).find(".to_archive").click(function(){
							window.open(BASE_URL+"/telenet/page.php?page=object_archiv&nID="+$(this).attr('oid'),'_blank','width=1080,height=515,resizable=no');
						});
						var el =  document.createElement("div");
						$(el).attr('title',"Информация за обект "+that.num).css('background-color','rgba(207, 239, 253, 0.496094)');
						$(el).append(tbl).append(tbla).attr('name','dialog');
						$("body").append(el);
						$(el).dialog({width: 500,hide: 'fade',show:'fade'})
						     .bind( "dialogclose", function(event, ui) {$(el).remove();});
					},function(resp){console.log(resp)},"objinfo");	
				}
			}			
		];	
		if (this.isMonitoring) {
			mainMenu.push(
				{txt:"Причина",submenu:smMonitoring}
			);
		} else {
			mainMenu.push({txt:"Причина",submenu:smReasons}
			);
		}
		that.createMenu({params:{gMenu:that.gMenu,svg:svg,owner:circle,pg:_g},list:mainMenu});	
		
		$(this.gMenu).attr('display','');		
	} 	 
	this.setTime = function(time){ 		
		var color = "green";                        
		var minutes = Math.floor(time / 60);
		var seconds = time % 60;	
		var sTime = minutes + ":" + (Math.abs(seconds) < 10 ? "0"+Math.abs(seconds) : Math.abs(seconds));

		(time >= this.reactionTimeNormal) && (color = "red");
		$(txtTimer).text(sTime).attr('fill',color);
	}
	this.showTimer = function(show) {
		$(gTimer).attr('display',show?'':'none')		
	}        
	this.setStatus = function(pkg) {				
		$(txtNum).text(this.num);
		if (this.statusService) {
			$(circle).attr("fill","pink").attr("stroke","red");  		
		} else if (this.statusAlarm) {			
			if (this.isMonitoring) $(circle).attr("fill","plum").attr("stroke","blue") 
			else $(circle).attr("fill","white").attr("stroke","blue");
		} else {
			$(circle).attr("fill-opacity","0").attr("stroke","blue");
		}
		if (!pkg) return;
		switch (pkg.event_type) {
			case "cancel":				
				this.showReason(pkg.id_reason);					
				setTimeout(function(){                              					
					that.showTimer(false);
					that.time=0;                
					$(_g).remove(); 
					that.showRange(0);
					//that.showReason(pkg.id_reason)
					delete geoLayers[that.idRegion].objSVG[that.id];
				},10000);
				//geoLayers[this.idRegion].opSVG.delObject(this.id);
			break;
			case "alarm":
				this.showReason(pkg.id_reason);	
				this.goLast();						
//				if (this.geo_lat > geoLayers[this.idRegion].zoomBounds.n ||
//				    this.geo_lat < geoLayers[this.idRegion].zoomBounds.s ||
//				    this.geo_lan < geoLayers[this.idRegion].zoomBounds.w ||
//				    this.geo_lan > geoLayers[this.idRegion].zoomBounds.e)
				fw.autoZoom(this.idRegion);				
				fw.saveDispatcherFactor(pkg);
			break;
			case "update":
				this.showReason(pkg.id_reason);	
			break;
		}		
		if (pkg.alarmElapsedTime!=null && this.statusAlarm && !range) {
			if(this.reactionTimeInterval) {
				clearInterval(this.reactionTimeInterval);
				this.reactionTimeInterval = null;
			}			
			this.alarmStartTime = (new Date()).getTime();
			this.alarmElapsedTime = pkg.alarmElapsedTime;
			this.showTimer(true);
			this.reactionTimeNormal = pkg.reactionTimeNormal;
			this.reactionTimeDifficult = pkg.reactionTimeDifficult;
			this.reactionTimeInterval = setInterval(function(){
				that.setTime(parseInt(((new Date()).getTime() - that.alarmStartTime)/1000) + that.alarmElapsedTime);
			}, 1000)
		} else {
			clearInterval(this.reactionTimeInterval);
			this.reactionTimeInterval=null;
		}
		for (var obj in geoLayers[this.idRegion].objSVG) {
			if (geoLayers[this.idRegion].objSVG[obj].statusAlarm) {
				geoLayers[this.idRegion].alarm = true;
				if (activeRegionId!=this.idRegion && !geoLayers[activeRegionId].alarm) {
					$("div[name=layer_"+this.idRegion+"]").parent().click();
				} else {
					fw.checkAalarm(this.idRegion);
				}
				break;
			} else {
				geoLayers[this.idRegion].alarm = false;
				fw.checkAalarm(this.idRegion);
			}
		}				
	}    
	this.showRange = function(show) {       
		if (show && !range) {		   
			range = oLayers[this.idRegion].circle($(circle).attr('cx'),$(circle).attr('cy'),sizeLevel*2,{'fill-opacity': "0.1",'fill': 'blue'});
			$(range).prependTo($(oLayers[this.idRegion]._container).find("svg"));
		} else if (!show) {
			$(range).remove();
			range = null;
		}
    }	
	this.showReason = function(id_reason) {			
		if (id_reason){			
			if ($(gReason).attr('display')=='none') {															
				$(reasonText).attr('x',$(circle).attr('cx'));
				$(reasonText).attr('y',$(circle).attr('cy')-45);				
				$(reasonText).text(alarmReasonsAll[id_reason]);						
				reasonRect.attributes.width.value  = $(reasonText).width()  + 4;
				reasonRect.attributes.height.value = $(reasonText).height() + 4;
				reasonRect.attributes.x.value = that.getNewX() - parseInt(reasonRect.attributes.width.value)/2 - 2;
				reasonRect.attributes.y.value = that.getNewY() - 60;				
				$(gReason).attr('display','');	
                                
			} else {
				$(reasonText).text(alarmReasonsAll[id_reason]);
				reasonRect.attributes.width.value  = $(reasonText).width()  + 4;
				reasonRect.attributes.height.value = $(reasonText).height() + 4;
				reasonRect.attributes.x.value = that.getNewX() - parseInt(reasonRect.attributes.width.value)/2 - 2;
				reasonRect.attributes.y.value = that.getNewY() - 60;
			}
			if (reasonTimeOut) {
				clearTimeout(reasonTimeOut);
				reasonTimeOut = null;
			}
			reasonTimeOut = setTimeout(function(){$(gReason).attr('display','none');},5000);
//			this.move($(circle).attr('cx'), $(circle).attr('cy'))
		}
		
    }    
	this.changeSize = function(size) {
		$(circle).attr("r",size/2);
		if (range) $(range).attr("r",size*2);		
		$(txtTimer).css('font-size',size/2+"px");
		$(txtNum).css('font-size',size/2+"px");
	}    
	this.move = function(x,y){                           
		if (range) {
			range.attributes.cx.value=x;
			range.attributes.cy.value=y;
		}				
		circle.attributes.cx.value = x;
		circle.attributes.cy.value = y;
		txtTimer.attributes.x.value = x-8;
		txtTimer.attributes.y.value = y+30;
		reasonRect.attributes.x.value = that.getNewX() - parseInt(reasonRect.attributes.width.value)/2 - 2;
		reasonRect.attributes.y.value = reasonRect.attributes.y.value < 10 ? y+60 : y-60;
		reasonText.attributes.x.value = x;
		reasonText.attributes.y.value = reasonText.attributes.y.value < 25 ? y+45 : y-45;
		rectNum.attributes.width.value = $(txtNum).width() + 4;
		rectNum.attributes.x.value = x - $(txtNum).width()/2 - 2;
		rectNum.attributes.y.value = y-sizeLevel*1.7;
		txtNum.attributes.x.value = x;
		txtNum.attributes.y.value = y-sizeLevel;
		this.newX = x;
		this.newY = y;
		(this.menuIsShown) && this.showMenu();
    };	
	this.doClick = function() {$(circle).click();}
	this.getImage = function() {return "img/house.png";}
	this.getHeader = function() {return this.num;}
	this.create();	
	if (draw) this.show();
}

//KOLI  poX, poY - parent offset X,Y
fw.carShape		 = function(svg,name,regn,x,y,draw,pkg,serReason) {        
	this.regnum = regn;
	this.callsign = name;
	this.status='free'; //free;announced;start;nocon;nosat;nosatnocon;service	
	this.idObject;
	this.id_attach_point;
	this.distance;
	this.event_type;
	this.statusGeo;
	this.statusService;
	this.statusConnection;
	this.statusReaction;
	this.aPointsGeo = [];
	this.gMenu;
	this.menuIsShown = false;
	this.geo_lat;
    this.geo_lan;
	this.reactionElapsedTime;	
	this.reactionTimeInterval;
	this.reactionTimeNormal;
	this.outLander = pkg.mainOffice != pkg.idRegion;
	this.elType = 'car';	
	this.serviceReason = serReason;
	this.serReason;
		
	var txtNum;
	var car;
	var carTablet;
	var rectNumCarTablet;
	var txtNumCarTablet;
	var line;
	var lineStart;	
	var txtDistance;
	var right=false;
	var shadowStart;
	var txtTimer;
	var gTimer;
	var rectNum;
	var aura;
	var that = this;     	
	var _g = svg.group({name:name,display:pkg.car_function==2?'':'none'});		
	fw.carShape.baseConstructor.call(this,x,y,_g);
	
	this.create = function() {			
        if (pkg.car_function!=2 && !this.outLander) {					
			car =  svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/carsFromRegion.png",{'car':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});		
		} else if (this.outLander) {	
			car =  svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/carsInRegion.png",{'car':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});		
		} else {			
			gTimer		= svg.group(_g,{svgX:this.X-15,svgY:this.Y+15,strokeWidth:1,name:name+'_timer',display:'none;',gTimer:1});        
			txtTimer	= svg.text(gTimer,0,0,'0:00',{fill:'green',style:'font-size:10px;text-anchor:middle;',name:name+"_txttimer",poY:sizeLevel+15,poX:sizeLevel/2,'carTimer':1});
			shadowStart = svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/car_shadowstart_64.png",{display:'none',shadowStart:'1'});
			lineStart	= svg.polyline(_g,[[this.newX+8,this.newY+2]],{display:'none','class':'lineStart',poY:'13',poX:'8'});
			car			= svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/car_free_64.png",{'car':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});                
			carTablet	= svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/car_tgps_64.png",{display:'none',carTablet:'1'});
			rectNumCarTablet = svg.rect(_g,0,0,sizeLevel/2,15,5,5,{'display':'none','class':'bgRect','rectNumCarTablet':1,poX:sizeLevel/2,poY:-sizeLevel});
			txtNumCarTablet	 = svg.text(_g,this.X+sizeLevel/2,this.Y-sizeLevel,this.callsign,{'display':'none',style:'text-anchor:middle;font-size:10px;',name:name+"_txtnum",txtNumCarTablet:'1',poX:sizeLevel/2,poY: -sizeLevel/2});						
			line			 = svg.line(_g,0,0,0,0,{'class':'line',display:'none',poX:+sizeLevel/2,poY:+sizeLevel/2,line:1});        
			txtDistance		 = svg.text(_g,10,10,that.distance || '0.0',{style:'font-size:10px;',name:name+"_txtdist",display:'none',txtDistance: '1'});                                                   		
		}		
        this.gMenu = svg.group(_g,{name:this.id+"_menu",display:"none"});        
		rectNum  = svg.rect(_g,0,0,sizeLevel/2,15,5,5,{'class':'bgRect','rectNum':1,poX:-sizeLevel/2,poY:-sizeLevel*1.6});
        txtNum = svg.text(_g,this.X+sizeLevel/2,this.Y-sizeLevel,'',{style:'text-anchor:middle;font-size:10px;',name:name+"_txtnum",txtNum:'1',poX:0,poY: -sizeLevel});		
		txtNum.textContent = this.car_function==2 ? this.callsign+'' : this.regnum;		
        $(car).mouseenter(function(){          											
			txtNum.textContent = that.car_function==2 ? that.callsign +'('+that.regnum+')' : that.regnum;  
			if ( that.car_function==2 && that.statusService ) {
				txtNum.textContent += ' - ' + that.serReason; 
			}         
			rectNum.attributes.width.value  = $(txtNum).width()  + 4;
			rectNum.attributes.height.value = $(txtNum).height() + 4;
			rectNum.attributes.x.value = that.getNewX() - parseInt(rectNum.attributes.width.value)/2;
			that.goLast();
        }).mouseleave(function(){			
            txtNum.textContent = that.car_function==2 ? that.callsign : that.regnum;
			rectNum.attributes.width.value  = $(txtNum).width()  + 4;
			rectNum.attributes.height.value = $(txtNum).height() + 4;
			rectNum.attributes.x.value = that.getNewX() - parseInt(rectNum.attributes.width.value)/2;	
        });      
		$(car).bind('contextmenu',function(e){fw.bunchSelector(e);});
		$(car).click(function(e){				
//			if (e.button==2) {								
//				fw.bunchSelector(e);
//				return;
//			}
			if (that.car_function!=2) {				
				fw.sendCommand("carinfo",{id_auto:that.id},function(resp){	
					var persons="";					
					if (resp.drivers && resp.drivers.length) {						
						for (var i=0;i<resp.drivers.length;i++) {
							persons+=resp.drivers[i].person_code + "  " +
							"<span name='showPersonInfo' person='"+resp.drivers[i].person_id+"' style='cursor:pointer; color:blue;'>"+resp.drivers[i].person_name+"</span>" + "  " +
							resp.drivers[i].person_phone;
							if (i<resp.drivers.length-1) persons+="<br>"
						}
					}
					var tbl = document.createElement("table");					
					$(tbl).attr({
						border:1,
						cellpadding:3,
						cellspacing:1,
						width:'100%'
					}).css({
						borderCollapse:'collapse'
					});
					$(tbl).append("<tr><td>Марка/модел</td><td>"+resp.mm+" / "+that.car_function_name+"</td></tr>")
						  .append("<tr><td>Регион</td><td>"+geoLayers[that.idRegion].name+"</td></tr>")
						  .append("<tr><td>Регистрационен номер</td><td>"+that.regnum+"</td></tr>")
						  .append("<tr><td>Километраж</td><td>"+resp.hash_km+"</td></tr>")
						  .append("<tr><td>Телефон</td><td>"+(resp.car_phone || '')+"</td></tr>")
						  .append("<tr><td>Водач</td><td>"+persons+"</td></tr>");
					$(tbl).find("td").attr("nowrap","nowrap")
					$(tbl).find("span[name=showPersonInfo]").click(function(){
						window.open(BASE_URL+"/telenet/page.php?page=personInfo&id="+$(this).attr('person'),'_blank','width=1080,height=515,resizable=no');
					});
					var el =  document.createElement("div");
					$(el).attr('title',"Информация за автомобил "+that.regnum).css('background-color','rgba(207, 239, 253, 0.496094)');
					$(el).html(tbl).attr('name','dialog');
					$("body").append(el);
					$(el).dialog({width: 500,hide: 'explode',show:'explode'});
				},function(resp){console.log(resp)},"carinfo");
				return;
			}	
			if(!that.menuIsShown) {
				that.menuIsShown = true;
				that.showMenu();			
				that.goLast();
				jQuery(document.body).bind('click.car-menu',function(){
					that.menuIsShown = false;
					that.destroyMenu();
					jQuery(document.body).unbind('click.car-menu');
				});
				e.stopPropagation();
			}
		});
    }    
	this.showMenu = function() {	    	    
	    if (!this.menuIsShown) {
		    $(this.gMenu).attr('display','none');
		    this.destroyMenu();
		    return;
	    } 
		var mainMenu=[];			
		var rejectFor = null;		
		var smReactionFor = [];	
		var smAnnounceFor = [];
		var smAlarmReasons = [];
		var smAlarmReasonsCancel = [];
		var smCarServiceReasons = [];
		
		if (this.status=="reaction")		
		for (var al in alarmReasonsPatrol) {						
			smAlarmReasons.push({txt : alarmReasonsPatrol[al],idAlarm:al,action : function(){
					fw.sendCommand('alarm_reason',{id_object:that.idObject,id_car:that.id,id_reason:this.idAlarm});
				}
			});
		}	
		
		for (var al in alarmReasonsCancelPatrol) {			
			smAlarmReasonsCancel.push({txt : alarmReasonsCancelPatrol[al],idReason:al,action : function(){
					fw.sendCommand('car_reject',{id_car:that.id,id_reason:this.idReason});
				}
			});
		}
		
		for (var al in alarmReasonsCancelPatrol) {			
			smCarServiceReasons.push({txt : alarmReasonsCancelPatrol[al],idReason:al,action : function(){
					fw.sendCommand('car_service',{id_car:that.id,id_reason:this.idReason});
				}
			});
		}
		
		for (var obj in geoLayers[this.idRegion].objSVG) {			
			if (geoLayers[this.idRegion].objSVG[obj].statusAlarm) {				
				if (obj!=this.idObject || that.statusReaction != 'reaction') {
					smReactionFor.push({
						txt : String(geoLayers[this.idRegion].objSVG[obj].num),
						obj:obj,
						action : function(){
							fw.sendCommand('car_react',{id_car:that.id,id_object:this.obj});
						}
					});
				}
				if (obj!=this.idObject) {
					smAnnounceFor.push({
						txt : String(geoLayers[this.idRegion].objSVG[obj].num),
						obj:obj,
						action : function(){
							fw.sendCommand('car_announce',{id_car:that.id,id_object:this.obj});
						}
					});
				}
			}
		}		
		if (this.idObject) {
			rejectFor = {
				txt : "Отказ за " + geoLayers[this.idRegion].objSVG[this.idObject].num,
				submenu:smAlarmReasonsCancel
			};		
		} 
		mainMenu.push({txt:"Набери",action:function(){that.setStatus('announce');}});
		if (rejectFor) mainMenu.push(rejectFor);
		if (this.status=="reaction") mainMenu.push({txt : "Причина за аларма", submenu : smAlarmReasons});		
		mainMenu.push({txt:"Анонс за",submenu:smAnnounceFor});	
		mainMenu.push({txt:"Реакция за",submenu:smReactionFor});
		mainMenu.push({txt:"Вкл. сервизен режим",submenu:smCarServiceReasons});
		mainMenu.push({txt:"Изкл. сервизен режим",action:function(){
				fw.sendCommand('car_cancel_service',{id_car:that.id});
			}
		});
		if (this.outLander) {
			mainMenu=[];
		}
		mainMenu.push({txt:"Информация",action:function(){
				fw.sendCommand("carinfo",{id_auto:that.id,callsign:that.callsign},function(resp){	
					var persons="";					
					if (resp.drivers && resp.drivers.length) {						
						for (var i=0;i<resp.drivers.length;i++) {
							persons+=resp.drivers[i].person_code + "  " +
							"<span name='showPersonInfo' person='"+resp.drivers[i].person_id+"' style='cursor:pointer; color:blue;'>"+resp.drivers[i].person_name+"</span>" + "  " +
							resp.drivers[i].person_phone;
							if (i<resp.drivers.length-1) persons+="<br>"
						}
					}
					var tbl = document.createElement("table");					
					$(tbl).attr({
						border:1,
						cellpadding:3,
						cellspacing:1,
						width:'100%'
					}).css({
						borderCollapse:'collapse'
					});
					$(tbl).append("<tr><td>Марка/модел</td><td>"+resp.mm+"</td></tr>")
						  .append("<tr><td>Регион</td><td>"+geoLayers[that.idRegion].name+"</td></tr>")
						  .append("<tr><td>Регистрационен номер</td><td>"+that.regnum+"</td></tr>")
						  .append("<tr><td>Километраж</td><td>"+resp.hash_km+"</td></tr>")
						  .append("<tr><td>Телефон</td><td>"+resp.car_phone+"</td></tr>")
						  .append("<tr><td>Патрул</td><td>"+persons+"</td></tr>")
						  .append("<tr><td><button name='btnCarGeo'>Координати</button></td><td id='tdCarGeo'></td></tr>");
					$(tbl).find("td").attr("nowrap","nowrap");
					$(tbl).find("span[name=showPersonInfo]").click(function(){
						window.open(BASE_URL+"/telenet/page.php?page=personInfo&id="+$(this).attr('person'),'_blank','width=1080,height=515,resizable=no');
					});					
					var tblCarGeo = document.createElement("table");
					$(tblCarGeo).attr({
						border:1,
						cellpadding:3,
						cellspacing:1,
						width:'100%'
					}).css({
						borderCollapse:'collapse',
						display:'none'
					});
					$(tblCarGeo).append("<tr><th></th><th>Координати</th><th>Време</th></tr>")
								.append("<tr><th>GPS</th><td>"+resp.geo_lat+"</br>"+resp.geo_lan+"</td><td>"+resp.geo_time+"</td></tr>")
								.append("<tr><th>Таблет</th><td>"+resp.client_geo_lat+"</br>"+resp.client_geo_lan+"</td><td>"+resp.client_geo_time+"</td></tr>")
								.append("<tr><th>Разлика</th><td align='center' colspan='2'>"+Math.round(fw.gpsDistance(resp.geo_lat, resp.client_geo_lat, resp.geo_lan, resp.client_geo_lan))/1000+" км</td></tr>");
					$(tbl).find("button[name=btnCarGeo]").click(function(){						
						$(tblCarGeo).css('display',$(tblCarGeo).css('display')=='none'?'':'none');
					});
					$(tbl).find("#tdCarGeo").html(tblCarGeo);
					var el =  document.createElement("div");
					$(el).attr('title',"Информация за патрул "+that.callsign).css('background-color','rgba(207, 239, 253, 0.496094)');
					$(el).html(tbl).attr('name','dialog');
					$("body").append(el);
					$(el).dialog({width: 520,hide: 'fade',show:'fade'})
						 .bind( "dialogclose", function(event, ui) {$(el).remove();});
				},function(resp){console.log(resp)},"carinfo");				
			}
		});			
		this.createMenu({params:{gMenu:that.gMenu,svg:svg,owner:car,pg:_g},list:mainMenu});	
		$(this.gMenu).attr('display','');
    }	
	this.showTimer = function(show) {
		$(gTimer).attr('display',show?'':'none')		
	} 	    
	this.showAura = function(show,type) {
		if (show && !aura) {
			aura = svg.circle(_g,this.getNewX(),this.getNewY(),sizeLevel/1.4,{'fill-opacity': "0.3",'fill': 'orange'});
			//$(aura).prependTo($(oLayers[this.idRegion]._container).find("svg"));
			$(aura).prependTo($(_g));
		} else if (!show) {
			$(aura).remove();
			aura = null;
		}
	}
	this.showCarTablet = function(show){			
		var _xyshow = Number($(carTablet).attr("x")) + Number($(carTablet).attr("y")) + show;		
		carTablet.attributes.display.value		= _xyshow > 1 && show ? "" :"none";
		rectNumCarTablet.attributes.display.value = _xyshow > 1 && show ? "" :"none";
		txtNumCarTablet.attributes.display.value = _xyshow > 1 && show ? "" :"none";		
	}
	this.setTime = function(time){ 		
		var color = "green";                        
		var minutes = Math.floor(time / 60);
		var seconds = time % 60;
		var sTime = minutes + ":" + (seconds < 10 ? "0"+seconds : seconds);
		(time >= this.reactionTimeNormal) && (color = "red");
		$(txtTimer).text(sTime).attr('fill',color);
	}	
	this.setStatus = function(pkg) { 				
		if (this.outLander) {			
			fw.autoZoom(this.idRegion);
			return;
		}
		$(txtNum).text(this.callsign);
		this.idObject= pkg.idObject || '';
		this.status = pkg.statusReaction;
		this.X = this.getNewX();
		this.Y = this.getNewY(); 
		if (pkg.idObject && geoLayers[this.idRegion].objSVG[pkg.idObject]) {			
			$(txtDistance)
					.attr('x',(parseInt($(line).attr('x1')) + parseInt($(line).attr('x2'))) / 2)
					.attr('y',(parseInt($(line).attr('y1')) + parseInt($(line).attr('y2'))) / 2)
					.text(Math.ceil(this.distance));
			$(line)
					.attr('x1',this.getNewX()+sizeLevel/2)
					.attr('y1',this.getNewY()+sizeLevel/1.5)
					.attr('x2',geoLayers[this.idRegion].objSVG[this.idObject].getNewX())
					.attr('y2',geoLayers[this.idRegion].objSVG[this.idObject].getNewY());    
			if (pkg.statusReaction=="announce") {
				$(car).attr("href","img/car_announce_64.png");
				$(line).removeClass('line').addClass('lineAnnounce').attr('display','');
				$(txtDistance).attr('display','');
				$(shadowStart).attr('display','none');
				$(lineStart).attr('display','none');
//				if (this.geo_lat > geoLayers[this.idRegion].zoomBounds.n ||
//				    this.geo_lat < geoLayers[this.idRegion].zoomBounds.s ||
//				    this.geo_lan < geoLayers[this.idRegion].zoomBounds.w ||
//				    this.geo_lan > geoLayers[this.idRegion].zoomBounds.e)
//					if (this.idRegion==activeRegionId) fw.autoZoom(this.idRegion);
				this.show();								
				this.showTimer(false);	
			} else if(pkg.statusReaction=="reaction") {				
				$(car).attr("href","img/car_start_64.png");
				$(line).removeClass('lineAnnounce').addClass('line').attr('display','');
				$(txtDistance).attr('display','');
				$(shadowStart).attr('display','');
				$(lineStart).attr('display','');
				this.reactionTimeNormal=pkg.reactionTimeNormal
				this.show();
				this.aPointsGeo.push({geo_lat:that.geo_lat,geo_lan:that.geo_lan});				
				this.showTimer(true);	
				this.changeSize(24);
			} 			
		} else {
			if (this.car_function==2) {
				$(car).attr("href","img/car_free_64.png");
				$(txtDistance).attr('display','none');
				$(shadowStart).attr('display','none');
				$(lineStart).attr('display','none');
				$(line).attr('display','none');
				if (!geoLayers[this.idRegion].visibles.cars) this.hide();
			}
			this.aPointsGeo=[];
			//that.aPointsGeo.push({geo_lat:that.geo_lat,geo_lan:that.geo_lan});			
			this.changeSize(sizeLevel);
			this.showTimer(false);	
		}
				
		switch (pkg.event_type) {
			case "start":				
				//$(shadowStart).attr('x',this.getNewX()).attr('y',this.getNewY());
				this.aPointsGeo[0] = {geo_lat:this.geo_lat,geo_lan:this.geo_lan};
				//if (this.idRegion==activeRegionId) fw.autoZoom(this.idRegion);
			break;
			case "arrival":				
				geoLayers[this.idRegion].objSVG[that.idObject].showRange(1);
				clearInterval(geoLayers[this.idRegion].objSVG[that.idObject].reactionTimeInterval);
				geoLayers[this.idRegion].objSVG[that.idObject].reactionTimeInterval=null;
			break;					
			case "reason":								
				geoLayers[this.idRegion].objSVG[pkg.id_reason_object].reason = alarmReasonsAll[pkg.id_reason];
				geoLayers[this.idRegion].objSVG[pkg.id_reason_object].showReason(pkg.id_reason);
				this.showTimer(false);
			break;
			case "car_remove":
				$(this.shape).remove();
				delete geoLayers[this.idRegion].carSVG[this.id];
			break;
		}
		if (this.car_function==2) {			
			!(that.statusGeo  |  !that.statusConnection)	&& $(car).attr("href","img/car_nosat_64.png")	||
			!(!that.statusGeo |   that.statusConnection)	&& $(car).attr("href","img/car_nocon_64.png")	||
			(!that.statusGeo && !that.statusConnection)	&& $(car).attr("href","img/car_nosatnocon_64.png");		
		}
		 
		 if (that.statusService) {
			 $(car).attr("href","img/car_service_64.png");
			 //service_status_reason 
		 }
		 if (pkg.arrivalTime) {
			 clearInterval(this.reactionTimeInterval);
			 this.reactionTimeInterval = null;
		 }
		 if (pkg.alertnessCheckTime) {
			this.showAura(true);
		 } else {
			this.showAura(false);
		 }
		 if (pkg.statusReaction=="reaction" && !pkg.arrivalTime) {
				if (pkg.arrivalTime) return;
				if(this.reactionTimeInterval) {
					clearInterval(this.reactionTimeInterval);
					this.reactionTimeInterval = null;
				}
				this.alarmStartTime = (new Date()).getTime();
				this.reactionElapsedTime = pkg.reactionElapsedTime;
				this.showTimer(true);				
				this.reactionTimeInterval = setInterval(function(){
					that.setTime(parseInt(((new Date()).getTime() - that.alarmStartTime)/1000) + that.reactionElapsedTime);
				}, 1000)
			} else {
				clearInterval(this.reactionTimeInterval);
				this.reactionTimeInterval=null;				
			}
			if (this.geo_lat > geoLayers[this.idRegion].zoomBounds.n ||
				this.geo_lat < geoLayers[this.idRegion].zoomBounds.s ||
				this.geo_lan < geoLayers[this.idRegion].zoomBounds.w ||
				this.geo_lan > geoLayers[this.idRegion].zoomBounds.e)
				if (this.idRegion===activeRegionId) fw.autoZoom(this.idRegion);
	}	
	this.move = function(x,y,animate,aPointsPixel,tx,ty) {		
		animate = animate || false;		
		var x2,y2;			
		if (this.idObject && geoLayers[this.idRegion].objSVG.hasOwnProperty(this.idObject)) {
			x2 = geoLayers[this.idRegion].objSVG[this.idObject].getNewX();
			y2 = geoLayers[this.idRegion].objSVG[this.idObject].getNewY();			
		}		
		if (aura) {
			aura.attributes.cx.value=x;
			aura.attributes.cy.value=y;
		}	
		$(this.shape).find("*").each(function(index,child){                        
			var offsetX = parseInt($(child).attr("poX")) || 0;
			var offsetY = parseInt($(child).attr("poY")) || 0;   
			
			if ($(child).attr('x1')) {				
				(right) && (offsetX+=10);
				(animate) && 
				($(child).animate({svgX1 : x + sizeLevel/2, svgY1 : y  + sizeLevel/1.5})) ||
				//($(child).attr("x1",x + sizeLevel/2)) && ($(child).attr("y1",y  + sizeLevel/1.5)) && 
				($(child).attr("x1",x)) && ($(child).attr("y1",y)) && ($(child).attr("x2",x2)) && ($(child).attr("y2",y2));				
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
				var sPoints="";
				for (var i in aPointsPixel) {						
					//sPoints += (aPointsPixel[i].px + offsetX) + "," + (aPointsPixel[i].py + offsetY) + " ";					
					sPoints += (aPointsPixel[i].px) + "," + (aPointsPixel[i].py) + " ";					
				}						
				lineStart.attributes.points.value = sPoints;				
			} else if ($(child).attr("rectNum")) {				
				var rnX = x - parseInt(rectNum.attributes.width.value) / 2;
				rectNum.attributes.width.value = $(txtNum).width() + 4;
				rectNum.attributes.height.value = $(txtNum).height() + 4;				
				$(child).attr("x",rnX).attr("y",y + offsetY);
				if (y<=30) $(child).attr("x", rnX).attr("y", y - offsetY); 
			} else if ($(child).attr('carTimer')) {
				$(child).attr('x',x).attr('y',y+sizeLevel+5);
			} else if ($(child).attr('carTablet')) {				
				$(child).attr("x",tx + offsetX).attr("y",ty + offsetY);							
			} else if ($(child).attr('rectNumCarTablet')){												
				$(rectNumCarTablet).attr({width : $(txtNumCarTablet).width() + 4,height : $(txtNumCarTablet).height() + 4});				
				var rnX = tx - parseInt(rectNumCarTablet.attributes.width.value) / 2;
				$(child).attr({x : rnX + offsetX,y : ty + offsetY});
				 (y<=30) && $(child).attr({x : rnX,y : ty - offsetY});												
			} else if ($(child).attr('txtNumCarTablet')){
				$(child).attr("x",tx + offsetX).attr("y",ty + offsetY);	
			} else {
				(animate) &&
				($(child).animate({svgX : x + offsetX, svgY : y + offsetY})) ||									
				($(child).attr("x",x + offsetX).attr("y",y + offsetY));				
				if (y<=30 && $(child).attr("txtNum")) {
					$(child).attr("x", x + offsetX).attr("y", y - offsetY + sizeLevel*1.2);
				}
			}
		});
		this.newX = x;
		this.newY = y;
		(this.menuIsShown) && this.showMenu();
	}   
	this.changeSize = function(size) {
		$(car).attr({width:size,height:size});
		$(shadowStart).attr({width:size,height:size});
		 (carTablet) && $(carTablet).attr({width:size,height:size});
		$(txtTimer).css('font-size',size/2+"px");
		$(txtNum).css('font-size',size/2+"px");
		 (txtNumCarTablet) && $(txtNumCarTablet).css('font-size',size/2+"px");
		$(rectNum).attr({width : $(txtNum).width() + 4,height : $(txtNum).height() + 4});		
		 (rectNumCarTablet) && $(rectNumCarTablet).attr({width : $(txtNumCarTablet).width()  + 4,height: $(txtNumCarTablet).height() + 4});		 
		$(txtDistance).css('font-size',size/2+"px");		
	} 
	this.setIcon = function(path) {car.attributes.href.value=path;}	
	this.doClick = function() {$(car).click()};		
	this.getImage = function() {return $(car).attr('href');}
	this.getHeader = function() {return this.callsign + "(" + this.regnum + ")";}
 	this.create();		
	if (pkg.car_function!=2) this.hide();
	if (draw) this.show();	
}

//STOQANKI
fw.waypointShape = function(svg,name,desc,x,y,draw) {    
	this.geo_lat;
	this.geo_lan;
	this.description;
	this.elType = 'waypoint';
		
	var nameWP;	
	var descWP;
	var rectName;
	var rectDesc;
	var _g = svg.group({name:name,display:'none'});
	var gNameDesc = svg.group(_g,{display:"none"});
	var wp;
	var that = this;
	fw.objectShape.baseConstructor.call(this,x,y,_g);

	this.create = function() {    				
		(!name) && (name="Безименна");
		(!desc) &&(desc="Без описание");		
		wp = svg.image(_g,this.X,this.Y, sizeLevel, sizeLevel, "img/wp.png",{'wp':'1',poX:'-'+sizeLevel/2,poY:'-'+sizeLevel/2});                
		rectName  = svg.rect(gNameDesc,0,0,0,20,5,5,{'class':'bgRect'});
		nameWP	  = svg.text(gNameDesc,0,0,name,{'class':'reasonText'});		
		rectDesc  = svg.rect(gNameDesc,0,0,0,20,5,5,{'class':'bgRect'});
		descWP	  = svg.text(gNameDesc,0,0,desc,{'class':'reasonText'});
		$(wp).bind('contextmenu',function(e){fw.bunchSelector(e);});		
		$(wp).mouseenter(function(){ 			
			rectName.attributes.width.value = $(nameWP).width() + 4;			
			rectName.attributes.x.value = that.getNewX() - rectName.attributes.width.value / 2;
			if (rectName.attributes.x.value < 0) rectName.attributes.x.value = 2;
			if (rectName.attributes.x.value > wContW - rectName.attributes.width.value) rectName.attributes.x.value = wContW - rectName.attributes.width.value - 2;
			rectName.attributes.y.value = that.getNewY() - sizeLevel - 15;
            rectDesc.attributes.width.value = $(descWP).width() + 4;
			rectDesc.attributes.x.value = that.getNewX() - rectDesc.attributes.width.value / 2;
			if (rectDesc.attributes.x.value < 0) rectDesc.attributes.x.value = 2;
			if (rectDesc.attributes.x.value > wContW - rectDesc.attributes.width.value) rectDesc.attributes.x.value = wContW - rectDesc.attributes.width.value - 2;
			rectDesc.attributes.y.value = that.getNewY() + sizeLevel - 5;
			nameWP.attributes.x.value = that.getNewX();
			if (nameWP.attributes.x.value  < $(nameWP).width()/2) nameWP.attributes.x.value = $(nameWP).width()/2 + 4;
			if (nameWP.attributes.x.value > wContW - $(nameWP).width()/2) nameWP.attributes.x.value =  wContW - $(nameWP).width()/2 - 4;
			nameWP.attributes.y.value = that.getNewY() - sizeLevel;
			descWP.attributes.x.value = that.getNewX();	
			if (descWP.attributes.x.value < $(descWP).width()/2) descWP.attributes.x.value = $(descWP).width()/2 + 4;			
			if (descWP.attributes.x.value > wContW - $(descWP).width()/2) descWP.attributes.x.value =  wContW - $(descWP).width()/2 - 4;
			descWP.attributes.y.value = that.getNewY() + sizeLevel + 10;
			that.goLast();
			$(gNameDesc).attr('display','');
        }).mouseleave(function(){
            $(gNameDesc).attr('display','none');			
        });
	}   
	this.changeSize = function(size) {
		$(wp).attr('width',size).attr('height',size);
//       var startX = $(poly).attr('points').getItem(0).x
//       var startY = $(poly).attr('points').getItem(0).y
//       var h = Math.sqrt(Math.pow(size/2,2) - Math.pow(size/4,2));
//       $(poly).attr('points').getItem(1).x = startX + h;
//       $(poly).attr('points').getItem(1).y = startY + size / 4;
//       $(poly).attr('points').getItem(2).y = startY + size / 2;
//       $(line).attr('y2',parseInt($(line).attr('y1')) + size * 0.7);
             
	}   
	this.move = function(x,y) {	
		$(this.shape).find("*").each(function(index,child){
			var offsetX = parseInt($(child).attr("poX")) || 0;
			var offsetY = parseInt($(child).attr("poY")) || 0;   				
			$(child).attr("x",x + offsetX).attr("y",y + offsetY)
		});
		rectName.attributes.width.value = $(nameWP).width() + 4;
		rectName.attributes.x.value = that.getNewX() - rectName.attributes.width.value / 2;
		rectName.attributes.y.value = that.getNewY() - sizeLevel - 15;
		rectDesc.attributes.width.value = $(descWP).width() + 4;
		rectDesc.attributes.x.value = that.getNewX() - rectDesc.attributes.width.value / 2;
		rectDesc.attributes.y.value = that.getNewY() + sizeLevel - 5;
		nameWP.attributes.y.value = that.getNewY() - sizeLevel;
		descWP.attributes.y.value = that.getNewY() + sizeLevel + 10;
	  
//      var _size = sizeLevel;
//      y-=_size;
//      var h = Math.sqrt(Math.pow(_size/2,2) - Math.pow(_size/4,2));
//      line.attributes.x1.value = x;
//      line.attributes.x2.value = x;
//      line.attributes.y1.value = y;
//      line.attributes.y2.value = y + 20;
//      var points = poly.attributes.points.value.split(" ");      
//      $.each(points,function(k,v){
//          var point = v.split(",");
//          switch (k) {
//              case 0:
//                  point[0]=x;
//                  point[1]=y
//                  points[k] = point.join(",")
//              break;
//              case 1:
//                  point[0]=x+h;
//                  point[1]=y+_size/4
//                  points[k] = point.join(",")
//              break;
//              case 2:
//                  point[0]=x;
//                  point[1]=y+_size/2
//                  points[k] = point.join(",")
//              break;
//          }          
//      });      
//      poly.attributes.points.value = points.join(" ");
	  this.newX = x;
      this.newY = y;
   }	
	this.setStatus = function(status) {
		if (status=='red') {
			$(wp).attr('href','img/wp_red.png');
		} else if (status=='yellow') {
			$(wp).attr('href','img/wp.png');
		}
	}	
	this.doClick = function() {$(wp).mouseenter();setTimeout(function() {$(wp).mouseleave();},3000);}	
	this.getImage = function() {return $(wp).attr('href');}	
	this.getHeader = function() {return name;}	
	this.create();	
	
	if (draw) this.show(); 
}

//DISPATCHER
fw.operatorShape = function(svg,name,x,y,draw) {
	this.attached = {};
	
	var kare;
	var text;	
	var lineText;
	var that = this;
	var _g = svg.group({name:name,display:'none'});
	fw.operatorShape.baseConstructor.call(this,x,y,_g);
	
	this.create = function() {
		if (kare) return;
		kare = svg.rect(_g,(wContW - 150)/2,y,150,20,5,5,{'class':'opShape'});
		text = svg.text(_g,wContW/2,y+15,'ДИСПЕЧЕР',{style:'font-size:12px;fill:red;text-anchor:middle;font-weight:bold;'});				
	}
	
	this.link = function(objId) {
		var _gl = svg.group(_g);
		var x1 = wContW/2;
		var y1 = 30;
		var x2 = geoLayers[this.idRegion].objSVG[objId].getNewX();
		var y2 = geoLayers[this.idRegion].objSVG[objId].getNewY();
		var path = svg.createPath();
		svg.path(_gl,path.move((x1+x2)/2,(y1+y2)/2).line(x1,y1),{id: 'disPath'+objId});
		var txtShow = geoLayers[this.idRegion].objSVG[objId].isMonitoring ? '' : 'none';
		var linkLine = svg.line(_gl,x1,y1,x2,y2,{'class':'line',poY:'10',poX:'7'});
		var text = svg.text(_gl,'',{style:'font-size:10px;',fill:'red',display:txtShow});
		var texts = svg.createText(); 				
		svg.textpath(text, '#disPath'+objId, texts.string('мониторинг'));
		
		return _gl;
	}
	
	this.addObject = function(objId) {
		if (this.attached.hasOwnProperty(objId)) return;				
		this.attached[objId] = this.link(objId);
		this.show();
	}
	
	this.delObject = function(objId) {				
		if (this.attached.hasOwnProperty(objId)) {			
			$(this.attached[objId]).remove()
			delete this.attached[objId];			
		}		
		
		if (!Object.keys(this.attached).length) this.hide();				
	}
	
	this.move = function(x,y,objId) {				
		if (this.attached.hasOwnProperty(objId)) {						
			var line	= $(this.attached[objId]).find('line')[0];
			var txtPath = $(this.attached[objId]).find('path')[0];
			var text	= $(this.attached[objId]).find('text')[0];
			var texts = svg.createText(); 
			line.attributes.x2.value = x;
			line.attributes.y2.value = y;							
			txtPath.attributes.d.value = String("M"+((wContW/2+x)/2)+","+((y+30)/2)+"L"+(wContW/2)+",30");	
			svg.textpath(text, '#disPath'+objId,texts);						
		}
	}	
	
	this.hasObject = function(objId) {
		if (this.attached.hasOwnProperty(objId)) return true;
		return false;
	}
	
	this.create();
	if (draw) this.show();
}

//WEBSOCKET
fw.serverCon	 = function() {		
	
	if(!("WebSocket" in window)) {
		if(("MozWebSocket" in window)) {
			window.WebSocket = MozWebSocket;
		} else {
			alert("Sorry, the build of your browser does not support WebSockets.");
			return;
		}
	}
	
	var sock = new WebSocket(WS_URL); 		
	sock.onopen		= function(evt) {	
		$("div[name=conStatus]").attr('title','Свързан');     
		$("div[name=conStatus] img").attr('src','img/plug-connect.png');               		
		var pkgInit = "init\t"+SESSION+"\t"+ID_PERSON+"-"+USER;
		sock.send(pkgInit);
		down = false;
		sounds.connect.play();
		return;
    };
	sock.onclose	= function(evt) {		
        $("div[name=conStatus]").attr('title','Прекъснат. Натисни за свързване');     
        $("div[name=conStatus] img").attr('src','img/plug-disconnect-slash.png');
		sounds.disconnect.load();
		sounds.disconnect.play();
        return;
    };
	sock.onerror	= function(evt) {      		
       $("div[name=conStatus]").attr('title',evt.data);   
       $("div[name=conStatus] img").attr('src','img/plug-disconnect-slash.png');
	   sounds.disconnect.load();
	   sounds.disconnect.play();
       return;
    };
	sock.onmessage	= function(evt) {    		
		var recv = JSON.parse(evt.data);
		
		if (monitoringOpen) fw.countBytesRcv(evt.data.length);		
		if (recv.confirmauth) {			
			fw.sendCommand('init',{id_person:ID_PERSON+" "+PERSON+" "+USER, session_id:SESSION});
			return true;
		}
		
		if (!isInit && recv.target_type!="init") return;				
		if (recv.target_type==="object") {			
			recv.idRegions.forEach(function(idRegion) {				
				var fakePacket = jQuery.extend({},recv);				
				fakePacket.idRegion = idRegion;					
				if (!geoLayers[fakePacket.idRegion].objSVG.hasOwnProperty(fakePacket.id))
					geoLayers[fakePacket.idRegion].objSVG[fakePacket.id] = new fw.objectShape(oLayers[fakePacket.idRegion], fakePacket.name, 0, 0, true);						
				(fakePacket['geo_lat']==0 || fakePacket['geo_lan']==0) && (fakePacket['geo_lat']=geoLayers[fakePacket.idRegion].geo_lan) && (fakePacket['geo_lan']=geoLayers[fakePacket.idRegion].geo_lat);				
				for (var objProp in fakePacket) {					
					geoLayers[fakePacket.idRegion].objSVG[fakePacket.id][objProp] = fakePacket[objProp];
				}
				geoLayers[fakePacket.idRegion].objSVG[fakePacket.id].setStatus(fakePacket);		
				fw.gpsRecalc(geoLayers[fakePacket.idRegion],"obj");				
			});			
			fw.alarmNotifications();
			//fw.attachToOperator();
		} else if (recv.target_type==="car") {			
			recv.idRegions.forEach(function(idRegion) {
				if (geoLayers[idRegion]) {
					var fakePacket = jQuery.extend({},recv);
					fakePacket.idRegion = idRegion;					
					if (!geoLayers[fakePacket.idRegion].carSVG.hasOwnProperty(fakePacket.id) && fw.checkRegionCars(fakePacket.idRegion,fakePacket.geo_lat,fakePacket.geo_lan,fakePacket.car_function)) {
						var _vis;
						if (fakePacket.car_function==2 && fakePacket.idRegion==fakePacket.mainOffice)
							_vis=geoLayers[idRegion].visibles.cars;
						else if (fakePacket.car_function==2 && fakePacket.idRegion!=fakePacket.mainOffice)
							_vis=geoLayers[idRegion].visibles.carsInRegion;
						else if(fakePacket.car_function!=2)
							_vis=geoLayers[idRegion].visibles.carsFromRegion;
						geoLayers[fakePacket.idRegion].carSVG[fakePacket.id] = new fw.carShape(oLayers[fakePacket.idRegion], fakePacket.callsign, fakePacket.regnum, 0, 0, _vis,fakePacket, fakePacket.serReason);					
					} 		

					for (var c in geoLayers[fakePacket.idRegion].carSVG) {
						if (c == fakePacket.id) {
							if (!fw.checkRegionCars(fakePacket.idRegion,fakePacket.geo_lat,fakePacket.geo_lan,fakePacket.car_function)) {
								$(geoLayers[fakePacket.idRegion].carSVG[c].shape).remove();
								delete geoLayers[fakePacket.idRegion].carSVG[c];								
								continue;
							}
							for (var carProp in fakePacket) geoLayers[fakePacket.idRegion].carSVG[c][carProp] = fakePacket[carProp];					
							geoLayers[fakePacket.idRegion].carSVG[c].setStatus(fakePacket);
							fw.gpsRecalc(geoLayers[fakePacket.idRegion],"car");	
						}						
					}
					//fw.attachToOperator();
				} //if region				
			});
		} else if(recv.target_type=="waypoint") {
			regionBounds = recv.region_bounds;			
			for (var idReg in recv.data){
				if (geoLayers[idReg]) { 
					for(var numCars in recv.data[idReg]) {
						var aWp = recv.data[idReg][numCars].split(',');
						var numPatruls = 0;
						$.each(geoLayers[idReg].carSVG,function(id,car){
							(car.car_function==2 && !car.outLander) && numPatruls++;
						});
						if (numPatruls == numCars) {
							for (var wp in geoLayers[idReg].wpSVG) {	
								var _a = $.inArray(wp, aWp)?'red':'yellow';								
								geoLayers[idReg].wpSVG[wp].setStatus($.inArray(wp, aWp)<=-1?'yellow':'red');
							}
						}
					}					
				}
			}
		} else if (recv.target_type=="init") {			
			if (isInit) return;	
			alarmReasonsPatrol = recv.alarmReasonsPatrol;
			alarmReasonsCancelPatrol = recv.alarmReasonsCancelPatrol;
			alarmReasonsBypassObject = recv.alarmReasonsBypassObject;
			alarmReasonsObjectMonitoring = recv.alarmReasonsObjectMonitoring;
			alarmReasonsObject = recv.alarmReasonsObject;
			nIDReasonNotify	= recv.nIDReasonNotify;
			alarmReasonsAll = recv.alarmReasonsAll;		
			regionBounds = recv.regionBounds;						
			
			var mid = Math.ceil(Object.keys(recv.regions).length/2);
			var regionTop = $("#regionTop");
			var regionBottom = $("#regionBottom");
			var numRegions = Object.keys(recv.regions).length;
			var skip = fw.activeLayer(recv.regions);                           		  
			for (var i=0;i<numRegions;i++) {                
				var idRegion = Object.keys(recv.regions)[i];  
				geoLayers[idRegion] = {									
					dbBounds : {
						w:recv.regions[idRegion].geo_w,
						s:recv.regions[idRegion].geo_s,
						n:recv.regions[idRegion].geo_n,
						e:recv.regions[idRegion].geo_e
					},
					zoomBounds : {
						w:recv.regions[idRegion].geo_w,
						s:recv.regions[idRegion].geo_s,
						n:recv.regions[idRegion].geo_n,
						e:recv.regions[idRegion].geo_e
					},
					geo_lat : recv.regions[idRegion].geo_lat,
					geo_lan : recv.regions[idRegion].geo_lan,
					visibles : {cars:0,carsFromRegion:0,carsInRegion:0,objects:0,wp:0,map:false,tgps:0},
					alarmInterval : 0,
					bgMapOff : true,
					alarm  : recv.regions[idRegion].alarm,
					name   : recv.regions[idRegion].name,
					region : idRegion,
					carSVG : {},
					objSVG : {},
					wpSVG  : {},
					opSVG  : {}
				};                                                                       
				 var _el = document.createElement("div");
				 var sk = i<9 ? i+1 : "Shift+" + String(i-9); 
				 $(_el).addClass('unfocusRegion')
					  .attr('name','selEl_'+idRegion).attr('key',i+1).attr('title',"Кратък клавиш "+sk)
					  .html("<div name='layer_"+idRegion+"' style='width:100%;height:100%; margin:0; padding:0;'></div>");
				 (i<mid) && (regionTop.append(_el)) || (regionBottom.append(_el)) ;
				 oLayers[idRegion] = fw.initSVG($(_el).find('div'));
				 var aWE = [];
				 var aNS = [];
				 for (var wp in recv.regions[idRegion].waitpoints) {                                                                                                                            
					geoLayers[idRegion].wpSVG[wp] = new fw.waypointShape(oLayers[idRegion], recv.regions[idRegion].waitpoints[wp].name, recv.regions[idRegion].waitpoints[wp].description, 0, 0, false);
					geoLayers[idRegion].wpSVG[wp].geo_lat = recv.regions[idRegion].waitpoints[wp].geo_lat;
					geoLayers[idRegion].wpSVG[wp].geo_lan = recv.regions[idRegion].waitpoints[wp].geo_lan;
					geoLayers[idRegion].wpSVG[wp].name = recv.regions[idRegion].waitpoints[wp].name;
					geoLayers[idRegion].wpSVG[wp].description = recv.regions[idRegion].waitpoints[wp].description;
					aWE.push(recv.regions[idRegion].waitpoints[wp].geo_lan);
					aNS.push(recv.regions[idRegion].waitpoints[wp].geo_lat);
				 }
				 if (aWE.length && aNS.length) {
					geoLayers[idRegion].zoomBounds.w = Math.min.apply(Math, aWE);
					geoLayers[idRegion].zoomBounds.e = Math.max.apply(Math, aWE);
					geoLayers[idRegion].zoomBounds.n = Math.max.apply(Math, aNS);
					geoLayers[idRegion].zoomBounds.s = Math.min.apply(Math, aNS);
				 }
				 $(_el).find("> div").prepend("<span class='regName' rsk='"+sk+"' regname='"+recv.regions[idRegion].name+"'>"+recv.regions[idRegion].name+"<span style='margin-left:5px;top:0;'></span></span><span class='rsk'></span>");
				 $(_el).click(function(evt) {	
					var nameIN;
					if ($(this).attr('id')=="joker" || $(this).find(" > div").attr('id')=="joker") {						
						return;					
					} else {
						nameIN = activeRegionId;
					}
					var _w = $(this).width();
					var _h = $(this).height();
					activeRegionId = $(this).find("div:eq(0)").attr('name').split("_")[1];   
					activeSVG = $(evt.currentTarget).find('div');
					if (geoLayers[activeRegionId].visibles.map) {						
						fw.autoZoom(activeRegionId);						
						$("#map_canvas").css('visibility','visible');
					} else {
						fw.gpsRecalc(geoLayers[activeRegionId],"all");
						$("#map_canvas").css('visibility','hidden');
					} 										
					$("div.wcontent > div > svg").attr('width',0).attr('height',0);										
					var nameOUT = $(evt.currentTarget).find('div').attr('name').split("_")[1];  
					$(evt.currentTarget).find('div').appendTo($("div.wcontent"));
					$(evt.currentTarget).addClass('unfocusRegionCurrent');
					$("div.wcontent > div:eq(0)").appendTo($("#joker").parent().removeClass('unfocusRegionCurrent'));
					$("#joker").html("<span style='margin:5px;position: relative;top:-1px; cursor:pointer;'>"+geoLayers[activeRegionId].name + "</span>")
					           .appendTo($(evt.currentTarget));					
					//$("div.wcontent > div:eq(0)").appendTo($(evt.currentTarget));
					$(evt.currentTarget).find('div > svg').attr('width', 0).attr('height',0);
					$("div.wcontent > div > svg").attr('width',$('.wcontent').width()).attr('height',$('.wcontent').height());
					if (nameIN) fw.checkAalarm(nameIN);
					fw.checkAalarm(nameOUT);				   				    					
					for (var vis in geoLayers[activeRegionId].visibles) {
						$("td[name=mbb2] li[name="+vis+"] input[ident="+vis+"]").attr('checked',geoLayers[activeRegionId].visibles[vis]);
						$("td[name=mbb4] li[name="+vis+"] input[ident="+vis+"]").attr('checked',geoLayers[activeRegionId].visibles[vis]);
						if (geoLayers[activeRegionId].visibles[vis]) {							
							$("td[name=mbb2] li[name="+vis+"] img").attr('src','img/'+vis+'.png');							
							$("td[name=mbb4] li[name="+vis+"] img").attr('src','img/'+vis+'.png');							
						} else {
							$("td[name=mbb2] li[name="+vis+"] img").attr('src','img/'+vis+'_grey.png');					
							$("td[name=mbb4] li[name="+vis+"] img").attr('src','img/'+vis+'_grey.png');							
						}
					}
					fw.autoZoom(activeRegionId);
					(archiveOpen) && $("#btnArchive").click();
					(serviceBypassOpen) && $("#btnServiceBypass").click();
					(dialogOpen) && $(".objectAlarmDialog").dialog("close");
					if (bSelector) {						
						$('#bunchSelectorTbl').remove();
						$('#activeRegion').unmousewheel();
						$("#bunchSelector").hide('explode',{},'fast').unbind();
						bSelector = false;
					}
				 });
				 fw.checkAalarm(idRegion);                              
				 geoLayers[idRegion].opSVG = new fw.operatorShape(oLayers[idRegion],'disp',0,10,false);
				 geoLayers[idRegion].opSVG.idRegion = idRegion;
			} 
			var rtcw = Number(wContW / regionTop.children().length - 5).toFixed();
			var rbcw = Number(wContW / regionBottom.children().length - 5).toFixed();
			regionTop.children().each(function(){$(this).css('width',rtcw);});
			regionBottom.children().each(function(){$(this).css('width',rbcw);});
			fw.checkNoCars();
			$("div[name=selEl_"+skip+"]").click();
			isInit=true;			
		}             
		if (isInit && (recv.target_type=="object" || recv.target_type=="car" && recv.car_function==2)) fw.attachToOperator();
		fw.checkNoCars();
        return;
    }   	
	wSock = sock;
    return sock;
}