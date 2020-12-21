<?
    ini_set("display_errors", 1);
	require_once("adodb/adodb.inc.php");

	$nDayHour = date("H", time());
	
	// По подразбиране - 6 часа между 9 и 18, 9 часа в извън работно време
	$nDeltaTime = !empty( $_GET['delta'] ) ? 
					$_GET['delta'] : 
					( $nDayHour > 17) || ( $nDayHour < 9 ) ? 9 : 6 ;
	
	// настройки на параметрите за достъп до сървърите
	$db_name = "eol_system";
	
	$db_master_host = '192.168.1.1:3306';
	$db_master_user = 'plamen';
	$db_master_pass = 'Plamen0S';

	$db_slave_host = '192.168.1.2:3307';
    $db_slave_user = 'plamen';
    $db_slave_pass = 'Plamen0S';
	
	$oDB_master = &ADONewConnection('mysqlt');
    $oDB_master->NConnect($db_master_host, $db_master_user, $db_master_pass, $db_name);
    //$oDB_master->debug=true;

    $oDB_slave = &ADONewConnection('mysqlt');
    $oDB_slave->NConnect($db_slave_host, $db_slave_user, $db_slave_pass, $db_name);
    //$oDB_slave->debug=true;
    
    // Текущо време се взема и ако денят е неделя не се пуска репликацията
    $nWeekDay = date("w", time());
    
    $sSystemLogTableName = sprintf("system_log_%04u%02u", date("Y", time()), date("m", time()) ); 
    
    if( $nWeekDay>0 )
    {
    	// Извличане информация от главния сървър
    	if( ! $rs = $oDB_master->Execute("SELECT UNIX_TIMESTAMP( MAX(time) ) as max_time FROM {$sSystemLogTableName}") )
    	{
    		printf( "[%s] sql_error : %s\n", date("Y-m-d H:i:s", time()), $oDB_master->ErrorMsg() );
    		return DBAPI_ERR_SQL_QUERY;	
    	}
    	
    	$aResult = $rs->fields;
    	$nMasterMaxTime = $aResult['max_time'];
    	
    	if( ! $rs = $oDB_master->Execute("SHOW MASTER STATUS") )
    	{
    		printf( "[%s] sql_error : %s\n", date("Y-m-d H:i:s", time()), $oDB_master->ErrorMsg() );
    		return DBAPI_ERR_SQL_QUERY;	
    	}

    	$aResult = $rs->fields;
		$sMasterFile= $aResult['File'];
		$sMasterPos	= $aResult['Position'];
		
    	// Извличане информация от подчинения сървър
    	$nSlaveMaxTime = time() - 10*60*60; // По подразбиране 10 часа назад
    	if( $rs = $oDB_slave->Execute("SELECT UNIX_TIMESTAMP( MAX(time) ) as max_time FROM {$sSystemLogTableName}") )
    	{
	    	$aResult = $rs->fields;
    		$nSlaveMaxTime = $aResult['max_time'];
    	}
    	
    	$nDeltaT = $nMasterMaxTime - $nSlaveMaxTime;
    	$nHourDelta = intval( $nDeltaT /(60*60) );
    	$nMinDelta = intval( ($nDeltaT - ($nHourDelta*60*60))/60 );
    	
    	if( $nDeltaT > ( $nDeltaTime*60*60) )
    	{
	    	printf( "[%s] (%u) delta: %02uh %02um - START SLAVE\n", date("Y-m-d H:i:s", time()), $nDeltaTime, $nHourDelta, $nMinDelta );
    		$oDB_slave->Execute("START SLAVE UNTIL MASTER_LOG_FILE = '{$sMasterFile}', MASTER_LOG_POS = {$sMasterPos}");
    	}
    	else 
    	{
	    	printf( "[%s] (%u) delta: %02uh %02um - STOP SLAVE\n", date( "Y-m-d H:i:s", time() ), $nDeltaTime, $nHourDelta, $nMinDelta );
			$oDB_slave->Execute("STOP SLAVE");
    	}
    }
    else 
    {
    	printf( "[%s] (sunday)- STOP SLAVE\n", date("Y-m-d H:i:s") );
    	$oDB_slave->Execute("STOP SLAVE");
    }

?>