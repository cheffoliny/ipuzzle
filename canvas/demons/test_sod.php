<?php
	DEFINE('MEDIATOR_URL'				, '213.91.252.138'	);
	DEFINE('MEDIATOR_PORT'				, 7001				);
	DEFINE('MONITORING_DEBUG'			, 4					);
	DEFINE('TIME_SLEEP'					, 1					);	// Време за изчакване межди събитията в сек
	    
	set_time_limit(3*60);

	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../../');
	
	require_once ("../../config/function.autoload.php");
    require_once ("../../config/config.inc.php");
	require_once ("../../config/connect.inc.php");
	require_once ("../../include/general.inc.php");

    
	$aTestEvents = array(
		array('type' => 'query'		, 'cmd' => 'insert into sod.archiv_<table> (id_msg, msg_time, num, status, msg, alarm) values(7256772, now(), 4992, 1, "ОБЩА АЛАРМА", 1)'),
		array('type' => 'command'	, 'cmd' => 'start'),
	
	);
	
	if( $db_host != '213.91.252.138')
		die('симулацията е предназначена само за ДЕМОТО!');
		
		
	// Инициализация на базата данни
	
		// Всички автомобили - в готовност
		$db_sod->Execute("UPDATE auto_trans.auto set reaction_status = 0, reaction_object = 0, reaction_time='0000-00-00 00:00:00'");
		// Всички обекти - в готовност
		$db_sod->Execute("update sod.objects set reaction_status=0, reaction_car=0");
		// Установяване на изходни координати на автомобил с рег номер Н 4729 ВА
		$db_sod->Execute("update auto.auto set geo_lat=43.269235, geo_lan=26.93601, geo_time=now(), geo_real_time=now()  where id=686");
		// Премахване на историята
		$db_sod->Execute("truncate table `sod`.alarm_history");
		$db_sod->Execute("truncate table `sod`.alarm_patruls");
		$db_sod->Execute("truncate table `sod`.alarm_register");
	
	$oDB = new DBBase2($db_sod, 'alarm_register');
	$oPatruls = new DBSODPatruls();
	
	foreach( $aTestEvents as $aEvent ){
		
		if( $aEvent['type']=='query' ){
			$sTable = date("Ym");
			$sQuery = $aEvent['cmd'];
			$sQuery = str_ireplace('<table>', $sTable, $sQuery);
			print("$sQuery\n");
				
			$db_sod->Execute($sQuery);
		} else {
			switch( $aEvent['cmd'] ){
				case 'start' :
					 $nMaxID = $oDB->selectOne('SELECT MAX(id) FROM sod.alarm_register');
					 $oPatruls->start( $nMaxID );
				break;
			}
		}

		 
	    sleep( TIME_SLEEP );
	}


