<?php
	if (!isset($_SESSION)) {
		session_start();
	}
	
	define ('NUM_ROWS', 10);
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../' );
		
	require_once("../config/function.autoload.php");
	require_once("../include/adodb/adodb-exceptions.inc.php");
	require_once("../config/connect.inc.php");
	require_once("../include/general.inc.php");
	
	global $db_sod, $db_name_sod;

	header("Content-type: text/html; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate"); 
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	$sResquestQueryType = !empty($_GET['query_type']) 	? $_GET['query_type'] 		: "";
	$sResquestValue		= !empty($_GET['val']) 			? addslashes($_GET['val']) 	: "";

	$obj 			= isset($_GET['nID']) ? $_GET['nID'] 	: 0;
	$last 			= isset($_GET['last']) 		? $_GET['last'] 		: 0;
	$periodFrom		= isset($_GET['pFrom']) 	? $_GET['pFrom'] 		: "";
	$periodFromH	= isset($_GET['pFromH']) 	? $_GET['pFromH'] 		: "";
	$periodTo		= isset($_GET['pTo']) 		? $_GET['pTo'] 			: "";
	$periodToH		= isset($_GET['pToH']) 		? $_GET['pToH'] 		: "";
	$bReact			= isset($_GET['nReact']) 	? $_GET['nReact'] 		: "false";
	$noTest 		= isset($_GET['noTest']) 	? $_GET['noTest'] 		: 0;
	$nNum 			= isset($_GET['num']) 		? $_GET['num'] 			: 0;
	$nMaxI 			= isset($_GET['max_i']) 	? $_GET['max_i'] 		: 0;
	$r_limit		= isset($_GET['r_limit']) 	? $_GET['r_limit'] 		: 1;
	$nLoss			= isset($_GET['nLoss']) 	? $_GET['nLoss'] 		: false;
	
	if ( $bReact == "true" ) {
		$nReact = 1;
	} else {
		$nReact = 0;
	}
	
	if ( $nLoss == "true" ) {
		$nLoss = 1;
	} else {
		$nLoss = 0;
	}	
			
	$periodFrom 	= !empty($periodFrom) 		? date("Y-m-d", jsDateToTimestamp($periodFrom)) : date("Y-m")."-01";
	$periodTo		= !empty($periodTo) 		? date("Y-m-d", jsDateToTimestamp($periodTo)) 	: date("Y-m")."-31";
	$periodFromH	= !empty($periodFromH) 		? $periodFromH.":00"	: "00:00:00";
	$periodToH		= !empty($periodToH) 		? $periodToH.":00"		: "23:59:59";
				
	$from			= $periodFrom." ".$periodFromH;
	$to				= $periodTo." ".$periodToH;
	
	$oObject		= new DBObjects();
	$aObject		= array();
	$dat1 			= array();
	$dat2 			= array();
	
	$dat1 			= explode("-", $periodFrom);
	$dat2 			= explode("-", $periodTo);

	$min_date_mon 	= isset($dat1[1]) ? $dat1[1] : date("m");
	$min_date_ye 	= isset($dat1[0]) ? $dat1[0] : date("Y");
	
	$max_date_mon 	= isset($dat2[1]) ? $dat2[1] : date("m");
	$max_date_ye 	= isset($dat2[0]) ? $dat2[0] : date("Y");	
	
	$tables 		= array();
	$months 		= array();
	$last_date		= "";
	
	$aObject		= $oObject->getByID($obj);
	$obj_num		= isset($aObject['num']) 				? $aObject['num'] 				: 0;
//	$obj_area		= isset($aObject['id_signal_area']) 	? $aObject['id_signal_area'] 	: 0;
	
	$tables 		= SQL_get_tables($db_sod, 'archiv_', '______');

	if ( strlen($last) > 7  ) {
		$last_date 	= substr($last, 0, 6);
	}
	
	foreach ( $tables as $val ) {
		if ( ($val >= "archiv_".$min_date_ye.$min_date_mon) && ($val <= "archiv_".$max_date_ye.$max_date_mon) ) {
			if ( !empty($last_date) && $val < "archiv_".$last_date ) {
				continue;
			}
			
			$months[] = $val;
		}				
	}	
	
	$data 	= array();

	// TODO: 
	$aData['obj'] 		= $obj;
	$aData['tables'] 	= $months;
	$aData['react'] 	= $nReact;
	$aData['from'] 		= $from;
	$aData['to'] 		= $to;
	$aData['no_test'] 	= $noTest;
	$aData['num']		= $nNum;
	
	$aAnno	= array();
	$aViss= array();
	$aImages= array();
	
	$alarm		= "";
	$warn		= "";
			
	foreach ( $aImages as $val ) {
		if ( $val['alarm'] == 2 ) { 
			$alarm 	.= !empty($alarm) 	? "@".$val['id_sig'].",".date("d.m H:i:s", $val['msg_time']).",".$val['msg'] : $val['id_sig'].",".date("d.m H:i:s", $val['msg_time']).",".$val['msg'];	
		} else {
			$warn 	.= !empty($warn) 	? "@".$val['id_sig'].",".date("d.m H:i:s", $val['msg_time']).",".$val['msg'] : $val['id_sig'].",".date("d.m H:i:s", $val['msg_time']).",".$val['msg'];	
		}
	}
	
	
	
	if ( !empty($obj) && (count($months) == 1) ) { 
		$mt = current($months);
		$yt = str_replace("archiv_", "", $mt); //substr($mt, 0, 4);
		//$id = substr($last, 7);
		//$id	= intval($last);
		
		$sQuery = "
			SELECT 
				a.id as id,
				m.id_obj,
				DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
				a.msg as msg,
				IF (m.is_phone > 0, 'phone', 'radio') as type,
				a.pass as passibility
				
			FROM {$db_name_sod}.`{$mt}` a 
			LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id
			
			WHERE 1 ";
		
		if ( !empty($nLoss) && !empty($obj_num) ) {
			$sQuery .= " AND ( a.num = {$obj_num} ) ";
		} else {
			$sQuery .= " AND m.id_obj = {$obj} ";
		}
		
		$sQuery .= " 
				AND a.msg_time >= '{$from}'
				AND a.msg_time <= '{$to}'
				AND a.id > {$last}
		";
		//#AND CONCAT('{$yt}', LPAD(a.id_arhiv, 8, '0000000')) > '{$last}' 
		if ( !empty($nReact) ) {
			$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
		}
		
		$sQuery .= " GROUP BY a.id ORDER BY a.msg_time DESC";
		
		if ( !empty($r_limit) ) {
			if ( !empty($nMaxI) ) {
				$sQuery .= " LIMIT {$nMaxI} ";
			}
		}

		$rs = $db_sod->Execute( $sQuery );

		if( $rs ) {
			$data = $rs->getArray();
		}
	} elseif ( !empty($obj) && (count($months) > 1) )  {
		$sQuery = "";
		
		for ( $i = 0; $i < count($months) - 1; $i++ ) {
			$mt = $months[$i];
			$yt = str_replace("archiv_", "", $mt);
			
			$sQuery .= "
				( SELECT 
					a.id as id,
					m.id_obj,
					a.msg_time as t,
					DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
					a.msg as msg,
					IF (m.is_phone > 0, 'phone', 'radio') as type,
					a.pass as passibility
					
				FROM {$db_name_sod}.`{$mt}` a 
				LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id

				WHERE 1 ";
				
			if ( !empty($nLoss) && !empty($obj_num) ) {
				$sQuery .= " AND ( a.num = {$obj_num} ) ";
			} else {
				$sQuery .= " AND m.id_obj = {$obj} ";
			}
			
			$sQuery .= " 		
					AND a.msg_time >= '{$from}'
					AND a.msg_time <= '{$to}'
			";
			
			if ( "archiv_".$max_date_ye.$max_date_mon == $mt ) {

				
				$sQuery .= " \n AND a.id > {$last} ";
			}
					
			if ( !empty($nReact) ) {
				$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
			}
			
			$sQuery .= " GROUP BY a.id
				)
			   	UNION 
			"; 		
		}
		
		$mt = end($months);
		$yt = str_replace("archiv_", "", $mt);
		
		$sQuery .= "
			( SELECT 
				a.id as id,
				m.id_obj,
				a.msg_time as t,
				DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
				a.msg as msg,
				IF (m.is_phone > 0, 'phone', 'radio') as type,
				a.pass as passibility
			FROM {$db_name_sod}.`{$mt}` a 
			LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id
			WHERE 1 ";
		
			if ( !empty($nLoss) && !empty($obj_num) ) {
				$sQuery .= " AND ( a.num = {$obj_num} ) ";
			} else {
				$sQuery .= " AND m.id_obj = {$obj} ";
			}
			
			$sQuery .= " 		
				AND a.msg_time >= '{$from}'
				AND a.msg_time <= '{$to}'			
		";				
				
		if ( !empty($nReact) ) {
			$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
		}
		//ORDER BY a.msg_time ASC 
		$sQuery .= " GROUP BY a.id )";	
					
		if ( !empty($r_limit) ) {
			if ( !empty($nMaxI) ) {
				$sQuery .= " ORDER BY t DESC LIMIT {$nMaxI} ";
			} else {
				$sQuery .= " ORDER BY t DESC ";
			}
		} else {
			$sQuery .= " ORDER BY t DESC ";
		}
		//LIMIT 200 		
		$rs = $db_sod->Execute( $sQuery );

		if( $rs ) {
			$data = $rs->getArray();
		}		
	}	

	$aKeys 			= array();
	$aMsgTime		= array();
	$nIDObj			= array();
	$aMsg			= array();
	$aType			= array();
	$aPass			= array();
	
	$nLast			= 0;

	krsort($data);			
	reset($data);
	
	foreach ( $data as $val ) {
		if ( empty($val['msg']) ) $val['msg'] = 'Сигнала не е описан';
		$aKeys[]		= sprintf( "'%s'", $val['id'] );
		$aMsgTime[] 	= sprintf( "'%s'", $val['msg_time']);
		$nIDObj[]		= sprintf( "'%d'", $val['id_obj'] );
		$aMsg[]			= sprintf( "'%s'", javascriptescape_deep($val['msg']) );
		$aType[]		= sprintf( "'%s'", $val['type']);
		$aPass[]		= sprintf( "'%d'", $val['passibility']);

	}
	
		
	printf("
		var key		= new Array( %s ); 
		var msgtime	= new Array( %s ); 
		var id_obj 	= new Array( %s ); 
		var msg		= new Array( %s ); 
		var type	= new Array( %s ); 
		var pass	= new Array( %s );
		var anno	= %d;
		var viss	= %d;
		var alarm1	= '%s';
		var warn1 	= '%s';", 
		implode(",", $aKeys), 
		implode(",", $aMsgTime),
		implode(",", $nIDObj), 
		implode(",", $aMsg), 
		implode(",", $aType), 
		implode(",", $aPass),
		$aAnno,
		$aViss,
		$alarm,
		$warn
	);
?>