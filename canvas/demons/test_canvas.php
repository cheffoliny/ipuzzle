<?php
	DEFINE('MEDIATOR_URL'				, '213.91.252.138'	);
	DEFINE('MEDIATOR_PORT'				, 7000				);
	DEFINE('MONITORING_DEBUG'			, 4					);
	DEFINE('TIME_SLEEP'					, 1					);	// Време за изчакване межди събитията в сек
	    
	set_time_limit(3*60);

	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../../');
	
	require_once ("../../config/function.autoload.php");
     require_once ("../../config/config.inc.php");
	require_once ("../../config/connect.inc.php");
	require_once ("../../include/general.inc.php");	
	class cMD extends MonitoringDaemon {
		public function initCanvas() {			
			$oChild = new MonitoringDaemon();
			$oChild->processCommands();
			$oChild->oWSSEvents->sendEvents();			
		}
	}
	$oMD = new cMD();	
	$oMD->initCanvas();
	
	$oWSS = new WSSEvents();
	$aTestEvents;
	switch ($_POST['simid']) {
		case "sim1":			
			$aTestEvents = array(
			    array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.921868,
					'geo_lat'			=> 43.276612,
					'statusReaction'	=> 'free'
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.921868,
					'geo_lat'			=> 43.276612,
					'statusReaction'	=> 'free'
				),
				array(
					'target_type'		=> 'object',
					'target'			=> 36014642,
					'event_type'		=> 'alarm',
					'id_msg'			=> 7265921,
					'id_sig'			=> 4,
					'alarm_time'		=> '2011-06-03 13:49:00',
					'alarm_name'		=> 'ОБЩА АЛАРМА',
					'id'				=> 36014642,
					'idRegion'		=> 20,
					'num'			=> 4992,
					'name'			=> 'ОФИС АЙ ТИ - С. ВЕЛИКИ 46 Г ЕТ.2 - ОФИС 4',
					'geo_lat'			=> 43.273694,
					'geo_lan'			=> 26.926782,
					'statusAlarm'		=> true,
					'statusService'	=> false,
					'alarmElapsedTime'	=> 0,
				     'obj_time_alarm_reaction' => 3*60
				),
				array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.921911,
					'geo_lat'			=> 43.275831,
					'statusReaction'	=> 'announce',
				     'idObject'		=> 36014642,
				     'distance'		=> 542
				),
				array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'start',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.921911,
					'geo_lat'			=> 43.275831,
					'statusReaction'	=> 'reaction',
				     'idObject'		=> 36014642,
				     'distance'		=> 413
				),
			     array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.92204,
					'geo_lat'			=> 43.274549,
					'statusReaction'	=> 'reaction',
				     'idObject'		=> 36014642,
				     'distance'		=> 250
				),
				array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.922684,
					'geo_lat'			=> 43.273643,
					'statusReaction'	=> 'reaction',
					'idObject'		=> 36014642,
					'distance'		=> 100
				),
			     array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.924787,
					'geo_lat'			=> 43.273768,
					'statusReaction'	=> 'reaction',
					'idObject'		=> 36014642,
					'distance'		=> 80
				),
			     array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'arrival',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.926742,
					'geo_lat'			=> 43.273634,
					'statusReaction'	=> 'reaction',
					'idObject'		=> 36014642,
					'distance'		=> 50
				),
			     array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'reason',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.926722,
					'geo_lat'			=> 43.273634,
					'statusReaction'	=> 'reaction',
					'idObject'		=> 36014642,
					'distance'		=> 30,
				     'reason'			=> 'САБОТАЖ'
				),
			    array(
					'target_type'		=> 'object',
					'target'			=> 36014642,
					'event_type'		=> 'cancel',
					'id_msg'			=> 7265921,
					'id_sig'			=> 4,
					'alarm_time'		=> '2011-06-03 13:49:00',
					'alarm_name'		=> 'ОБЩА АЛАРМА',
					'id'				=> 36014642,
					'idRegion'		=> 20,
					'num'			=> 4992,
					'name'			=> 'ОФИС АЙ ТИ - С. ВЕЛИКИ 46 Г ЕТ.2 - ОФИС 4',
					'geo_lat'			=> 43.273694,
					'geo_lan'			=> 26.926782,
					'statusAlarm'		=> false,
					'statusService'	=> false,
				),
			     array(
					'target_type'		=> 'car',
					'target'			=> 676,
					'event_type'		=> 'position',
					'id' 			=> 676,
					'idRegion'		=> 20,			
					'callsign'		=> '20',
					'regnum'			=> 'Н 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 26.927104,
					'geo_lat'			=> 43.274081,
					'statusReaction'	=> 'free',
										
				)
			);			
		break;
		case "sim2":
			$aTestEvents = array(			   
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> false,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.322542,
					'geo_lat'			=> 42.88697,
					'statusReaction'	=> 'free',
					
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'position',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> false,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.314474,
					'geo_lat'			=> 42.887411,
					'statusReaction'	=> 'free',
					
				),			    
			    array(
					'target_type'		=> 'object',
					'target'			=> 36014643,
					'event_type'		=> 'alarm',
					'id_msg'			=> 7265921,
					'id_sig'			=> 4,
					'alarm_time'		=> '2011-06-03 13:49:00',
					'alarm_name'		=> 'ОБЩА АЛАРМА',
					'id'				=> 36014643,
					'idRegion'		=> 61,
					'num'			=> 4992,
					'name'			=> 'ОФИС ГАБРОВО 1',
				     'geo_lan'			=> 25.313787,
					'geo_lat'			=> 42.892819,					
					'statusAlarm'		=> true,
					'statusService'	=> false,
					'alarmElapsedTime'	=> 0,
				     'obj_time_alarm_reaction' => 3*60
				),	
				array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.324173,
					'geo_lat'			=> 42.891561,
					'statusReaction'	=> 'announce',
					'distance'		=> 1200,
				    'idObject'		=> 36014643,
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'position',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.315504,
					'geo_lat'			=> 42.890366,
					'statusReaction'	=> 'announce',
					'distance'		=> 800,
				   'idObject'		=> 36014643,
				),		
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'start',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.324173,
					'geo_lat'			=> 42.891561,
					'statusReaction'	=> 'reaction',
					'distance'		=> 1001,
				   'idObject'		=> 36014643,
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'start',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.315397,
					'geo_lat'			=> 42.89153,
					'statusReaction'	=> 'reaction',
					'distance'		=> 542,
				   'idObject'		=> 36014643,
				),			    
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.321813,
					'geo_lat'			=> 42.892347,
					'statusReaction'	=> 'reaction',
					'distance'		=> 876,
				   'idObject'		=> 36014643,
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'position',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.315676,
					'geo_lat'			=> 42.89241,
					'statusReaction'	=> 'reaction',
					'distance'		=> 120,
				   'idObject'		=> 36014643,
				),			    
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.319624,
					'geo_lat'			=> 42.892473,
					'statusReaction'	=> 'reaction',
					'distance'		=> 542,
				   'idObject'		=> 36014643,
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'arrival',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.313727,
					'geo_lat'			=> 42.892760,
					'statusReaction'	=> 'reaction',
					'distance'		=> 50,
				   'idObject'		=> 36014643,
				),			
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.319624,
					'geo_lat'			=> 42.892473,
					'statusReaction'	=> 'free',
					
				),
			    array(
					'target_type'		=> 'object',
					'target'			=> 36014644,
					'event_type'		=> 'g',
					'id_msg'			=> 7265921,
					'id_sig'			=> 4,
					'alarm_time'		=> '2011-06-03 13:49:00',
					'alarm_name'		=> 'ОБЩА АЛАРМА',
					'id'				=> 36014644,
					'idRegion'		=> 61,
					'num'			=> 4993,
					'name'			=> 'ОФИС ГАБРОВО 2',
				     'geo_lan'			=> 25.322242,	
					'geo_lat'			=> 42.896246,				
					'statusAlarm'		=> true,
					'statusService'	=> false,
					'alarmElapsedTime'	=> 0,
				     'obj_time_alarm_reaction' => 3*60
				),	
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.319624,
					'geo_lat'			=> 42.892473,
					'statusReaction'	=> 'announce',
				   'idObject'			=> 36014644,
				   'distance'			=> 789
					
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'reason',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.313727,
					'geo_lat'			=> 42.892760,
					'statusReaction'	=> 'free',
					'distance'		=> 50,
				   'idObject'		=> 36014643,
				   'reason'		=> "Фалшива аларма"
				),					    			    
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'start',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.320911,
					'geo_lat'			=> 42.89329,
					'statusReaction'	=> 'reaction',
				   'idObject'			=> 36014644,
				   'distance'			=> 500
					
				),			    
			    array(
					'target_type'		=> 'object',
					'target'			=> 36014643,
					'event_type'		=> 'cancel',
					'id_msg'			=> 7265921,
					'id_sig'			=> 4,
					'alarm_time'		=> '2011-06-03 13:49:00',
					'alarm_name'		=> 'Фалшива аларма',
					'id'				=> 36014643,
					'idRegion'		=> 61,
					'num'			=> 4992,
					'name'			=> 'ОФИС ГАБРОВО 1',
				     'geo_lan'			=> 25.313787,
					'geo_lat'			=> 42.892819,
					'statusAlarm'		=> false,
					'statusService'	=> false,
				),
			    array(
					'target_type'		=> 'car',
					'target'			=> 201,
					'event_type'		=> 'position',
					'id' 			=> 201,
					'idRegion'		=> 61,			
					'callsign'		=> '62',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> false,			
					'geo_lan'			=> 25.313797,
					'geo_lat'			=> 42.892760,
					'statusReaction'	=> 'free',
					
				   
				),	
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'position',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 4729 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.322371,
					'geo_lat'			=> 42.89502,
					'statusReaction'	=> 'reaction',
				   'idObject'			=> 36014644,
				   'distance'			=> 500
					
				),
			    
			    array(
					'target_type'		=> 'car',
					'target'			=> 200,
					'event_type'		=> 'arrival',
					'id' 			=> 200,
					'idRegion'		=> 61,			
					'callsign'		=> '61',
					'regnum'			=> 'G 2134 ВА',
					'phoneNum'		=> '',
					'statusService'	=> false,
					'statusConnection'	=> true,
					'statusGeo'		=> true,			
					'geo_lan'			=> 25.322182,	
					'geo_lat'			=> 42.896186,
					'statusReaction'	=> 'reaction',
					'distance'		=> 50,
				   'idObject'		=> 36014644,
				),	
			   
			 );
		break;
		case "sim3":
			echo "33333333333";
		break;
		case "sim4":
			echo "444444444";
		break;
	}
	foreach ($aTestEvents as $aEvent) {
		$oWSS->addEvent($aEvent);
		$oWSS->sendEvents();				
		sleep(3);
	}			
	print_r($aTestEvents);
//43.270425,26.940966
//43.270894,26.938262
//43.271206,26.936502
//43.271175,26.934829
//43.270862,26.931739
//43.270706,26.929164
//43.270675,26.926289
//43.270644,26.924014
//43.270487,26.922469
//
//
//
//43.276612,26.921868
//43.275831,26.921911
//43.275268,26.921868
//43.274549,26.92204
//43.273643,26.922684
//43.273768,26.924787
//43.273893,26.92616
//43.274081,26.927104
	   
//	$aTestEvents = array(
//		array(
//			'target_type'		=> 'object',
//			'target'			=> 36014642,
//			'event_type'		=> 'alarm',
//			'id_msg'			=> 7265921,
//			'id_sig'			=> 4,
//			'alarm_time'		=> '2011-06-03 13:49:00',
//			'alarm_name'		=> 'възстан. на САБОТАЖ 3zone',
//			'id'				=> 36014642,
//			'idRegion'		=> 60,
//			'num'			=> 1403,
//			'name'			=> '/Ст.З/СКЛАДОВА БАЗА -САГОВ с.Пъстрен',
//			'geo_lat'			=> 42.257110656433426,
//			'geo_lan'			=> 25.738949775695794,
//			'statusAlarm'		=> true,
//			'statusService'	=> false,
//			'alarmElapsedTime'	=> 1307098143,
//			
//		),
//		array(
//			'target_type'		=> 'car',
//			'target'			=> 676,
//		     'event_type'		=> 'position',
//			'id' 			=> 676,
//			'idRegion'		=> 20,			
//			'callsign'		=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//		     'phoneNum'		=> '',
//		     'statusService'	=> false,
//		     'statusConnection'	=> true,
//			'statusGeo'			=> true,			
//			'geo_lan'			=> 26.93601,
//			'geo_lat'			=> 43.269235,
//		     'statusReaction'	=> 'free'
//		),
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'announce',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.93601,
//			'geo_lat'			=> 43.269235,
//			'distance'			=> 4812.358421,
//			'idObject'			=> 36013575
//		),
//	
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'start',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.98001,
//			'geo_lat'			=> 43.269835,
//			'distance'			=> 4323.166024,
//		    'idObject'			=> 36013575
//		),
//	
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'position',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.95601,
//			'geo_lat'			=> 43.27,
//			'distance'			=> 2397.527033,
//		    'idObject'			=> 36013575
//		),
//	array(
//			'id'				=> 36013576,
//			'idRegion'			=> 20,
//			'event_type'		=> 'alarm',
//			'num'				=> 4993,
//			'name' 				=> 'ОФИС МОФИС',
//			'mainSig'			=> 'ОБЩА АЛАРМА',
//			'reactionTime'		=> 3*60,
//			'geo_lan'			=> 26.908522,
//			'geo_lat'			=> 43.274393,
//			'statusAlarm'		=> TRUE,
//			'statusServise'		=> FALSE,
//	),
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'position',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.93,
//			'geo_lat'			=> 43.272,
//			'distance'			=> 320.9301034,
//		    'idObject'			=> 36013575
//		),
//	
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'position',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.92688213,
//			'geo_lat'			=> 43.27319414,
//			'distance'			=> 56.08548193,
//		    'idObject'			=> 36013575
//		),
//		
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'arrival',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.92688213,
//			'geo_lat'			=> 43.27319414,
//			'distance'			=> 56.08548193,
//		    'idObject'			=> 36013575
//		),
		
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'reason',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.92678113,
//			'geo_lat'			=> 43.27370414,
//			'distance'			=> 1.113282204,
//			'reason'			=> 'Техническа',
//		    'idObject'			=> 36013575
//		),
//		
//		array(
//			'id' 				=> 686,
//			'idRegion'			=> 20,
//			'event_type'		=> 'car_free',
//			'callsign'			=> '20',
//			'regnum'			=> 'Н 4729 ВА',
//			'statusGeo'			=> true,
//			'statusConnection'	=> true,
//			'geo_lan'			=> 26.92678113,
//			'geo_lat'			=> 43.27370414,
//			'distance'			=> 1.113282204,
//			'reason'			=> 'Техническа',
//		    'idObject'			=> false
//		),
//		
//		array(
//			'id'				=> 36013575,
//			'idRegion'			=> 20,
//			'event_type'		=> 'cancel',
//			'num'				=> 4992,
//			'name' 				=> 'ОФИС АЙ ТИ - С. ВЕЛИКИ 46 Г ЕТ.2 - ОФИС 4',
//			'mainSig'			=> 'ОБЩА АЛАРМА',
//			'reactionTime'		=> 3*60,
//			'geo_lan'			=> 26.9267821311951,
//			'geo_lat'			=> 43.2736941397687,
//			'statusAlarm'		=> FALSE,
//			'statusServise'		=> FALSE,
//			'reason'			=> 'Техническа'
//		),
		
	
//	);
	
	
//	$oWSSEvents = new WSSEvents();	
//	//MonitoringEvents::send();
//	foreach( $aTestEvents as $aEvent ){
//		$oWSSEvents->addEvent($aEvent);
//		$oWSSEvents->sendEvents();
//	     sleep( TIME_SLEEP );
//	}
?>
