<?php
	require_once("include/adodb/adodb.inc.php");
	
	define('CLIENT_MULTI_STATEMENTS', 0x00010000);
	global $db_name;
	global $db_name_system;
	global $db_name_auto;
	global $db_name_finance;
	global $db_name_personnel;
	global $db_name_sod;
	global $db_name_storage;
	global $db_name_telepol;
	global $db_name_pbx;
	
	global $db_system;
	global $db_auto;
	global $db_finance;
	global $db_personnel;
	global $db_sod;
	global $db_storage;	
	global $db_telepol;
	global $db_pbx;
	
	$ADODB_FORCE_TYPE = ADODB_FORCE_EMPTY;
	
	$db_host = '213.91.252.129';
	$db_user = 'lamerko';
	$db_pass = 'Olig0fren';
		
	$db_host_backup = '213.91.252.129';
	$db_user_backup = 'lamerko';
	$db_pass_backup = 'Olig0fren';
	
	//$db_name			= 'telenet_test2';
	$db_name_system 	= 'telenet_system_test2';
	$db_name_auto		= 'auto_test2';
	$db_name_finance 	= 'finance_test2';
	$db_name_personnel	= 'personnel_test2';
	$db_name_sod		= 'sod_test2';
	$db_name_storage	= 'storage_test2';
	$db_name_pbx 		= 'pbx_test2';
	
	$db_system  	= new DBSmartConnection('db_system'		, $db_host, $db_user, $db_pass, $db_name_system 	);
	$db_auto 		= new DBSmartConnection('db_auto'		, $db_host, $db_user, $db_pass, $db_name_auto 		);
	$db_finance 	= new DBSmartConnection('db_finance'	, $db_host, $db_user, $db_pass, $db_name_finance 	);
	$db_personnel 	= new DBSmartConnection('db_personnel'	, $db_host, $db_user, $db_pass, $db_name_personnel 	);
	$db_sod 		= new DBSmartConnection('db_sod'		, $db_host, $db_user, $db_pass, $db_name_sod 		);
	$db_storage		= new DBSmartConnection('db_storage'	, $db_host, $db_user, $db_pass, $db_name_storage 	);
	$db_pbx			= new DBSmartConnection('db_pbx'		, $db_host, $db_user, $db_pass, $db_name_pbx 		);
	
	$db_system_backup		= new DBSmartConnection('db_system'		, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_system 	);
	$db_auto_backup 		= new DBSmartConnection('db_auto'		, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_auto 		);
	$db_finance_backup		= new DBSmartConnection('db_finance'	, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_finance 	);
	$db_personnel_backup	= new DBSmartConnection('db_personnel'	, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_personnel );
	$db_sod_backup 			= new DBSmartConnection('db_sod'		, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_sod 		);
	$db_storage_backup		= new DBSmartConnection('db_storage'	, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_storage 	);
	$db_pbx_backup 			= new DBSmartConnection('db_pbx'		, $db_host_backup, $db_user_backup, $db_pass_backup, $db_name_pbx 		);
	
	//POWER LINK CONECTION
	
	$db_host2 = '213.91.252.175';
	$db_user2 = 'lamerko';
	$db_pass2 = 'Olig0fren';
	
	$db_name_telepol = 'telepol';

    $db_telepol = &ADONewConnection('mysql');
    $db_telepol->SetFetchMode(ADODB_FETCH_ASSOC);
    $db_telepol->NConnect($db_host2,$db_user2,$db_pass2,$db_name_telepol);
	//$db_telepol = $db_sod;
//	if ( $db_sod->Execute("SELECT SEC_TO_FIXTIME(324,1) AS s") === false ) {
//		// Poradi limit vyv funkciite za tabota s time se nalaga zamestvane na sec_to_time() i time_format();
//		$sQry = "
//			CREATE DEFINER=`lamerko`@`localhost` FUNCTION `sod`.`sec_to_fixtime`(tsec bigint, flag tinyint)
//				RETURNS CHAR(12)
//				BEGIN
//				DECLARE tmp_hours REAL DEFAULT 0;
//				DECLARE tmp_minuts INT DEFAULT 0;
//				DECLARE tmp_seconds INT DEFAULT 0;
//
//				SET tmp_hours = TRUNCATE((tsec / 3600),0);
//				SET tmp_minuts = TRUNCATE(((tsec - (tmp_hours * 3600)) / 60), 0);
//				SET tmp_seconds = TRUNCATE(tsec - ((tmp_hours * 3600) + (tmp_minuts * 60)), 0);
//				
//				RETURN IF ( flag, CONCAT( IF ( tmp_hours > 0, tmp_hours, '00' ), ':', IF ( tmp_minuts > 0, tmp_minuts, '00' ), ':', IF ( tmp_seconds > 0, tmp_seconds, '00' ) ), CONCAT( IF ( tmp_hours > 0, tmp_hours, '00' ), ':', IF ( tmp_minuts > 0, tmp_minuts, '00' ) ) );
//				END;
//		";
//		
//		$db_sod->Execute($sQry);
//	}	
	
?>