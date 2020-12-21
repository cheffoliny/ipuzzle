<?php
	if (!isset($_SESSION)) 
		session_start();
	
	define ('NUM_ROWS', 10);
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
		set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
		
	require_once ("../config/function.autoload.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");

	header("Content-type: text/html; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past

	$sResquestQueryType = !empty($_GET['query_type']) ? $_GET['query_type'] : '';
	$sResquestValue		= !empty($_GET['val']) ? addslashes($_GET['val']) : '';

	$obj = isset($_GET['obj']) ? $_GET['obj'] : "";
	$last = isset($_GET['last']) ? $_GET['last'] : "";
	$data = array();
	$mt = date("Y_m");
	
	if ( !empty($obj) ) { 

		$sQuery = "
			SELECT 
				a.id_arhiv as id,
				DATE_FORMAT(a.MsgTime, '%d.%m.%Y %H:%i:%s') as msg_time,
				a.num, 
				o.name,
				a.Msg as msg,
				IF (m.is_phone > 0, 'phone', 'radio') as type,
				a.pass as passibility
			FROM 
				`{$mt}` a 
			LEFT JOIN messages m ON a.id_msg = m.id_msg
			LEFT JOIN objects o ON m.id_obj = o.id_obj
			WHERE a.id_arhiv > '{$last}'
				AND a.num = '{$obj}'
			ORDER BY a.id_arhiv DESC
			LIMIT ".NUM_ROWS;
		
		//echo $sQuery; AND a.num = '{$obj}'
		$rs = $db_telepol->Execute( $sQuery );

		if( $rs ) {
			$data = $rs->getArray();
		}
	}

	$aKeys 			= array();
	$aMsgTime		= array();
	$aNum			= array();
	$aObjects 		= array();
	$aMsg			= array();
	$aType			= array();
	$aPass			= array();

	foreach ( $data as $val ) {
		if ( empty($val['msg']) ) $val['msg'] = 'Сигнала не е описан';
		$aKeys[]	= sprintf( "'%d'", $val['id'] );
		$aMsgTime[] = sprintf( "'%s'", $val['msg_time']);
		$aNum[]		= sprintf( "'%d'", $val['num'] );
		$aObjects[] = sprintf( "'%s'", javascriptescape_deep(iconv('CP1251', 'UTF-8', $val['name'])) );  //$val['name']
		$aMsg[]		= sprintf( "'%s'", javascriptescape_deep(iconv('CP1251', 'UTF-8', $val['msg'])) );  //$val['msg']
		$aType[]	= sprintf( "'%s'", $val['type']);
		$aPass[]	= sprintf( "'%d'", $val['passibility']);
	}
	
	krsort($aKeys);		reset($aKeys);
	krsort($aMsgTime);	reset($aMsgTime);
	krsort($aNum);		reset($aNum);
	krsort($aObjects);	reset($aObjects);
	krsort($aMsg);		reset($aMsg);
	krsort($aType);		reset($aType);
	krsort($aPass);		reset($aPass);
	
	printf("
		var key		= new Array( %s ); 
		var msgtime	= new Array( %s ); 
		var num 	= new Array( %s ); 
		var object	= new Array( %s ); 
		var msg		= new Array( %s ); 
		var type	= new Array( %s ); 
		var pass	= new Array( %s );", 
		implode(",", $aKeys), 
		implode(",", $aMsgTime),
		implode(",", $aNum), 
		implode(",", $aObjects), 
		implode(",", $aMsg), 
		implode(",", $aType), 
		implode(",", $aPass) 
	);
	
	//echo $db_name_sod;
	//echo "test: ".$data[0]['msg'];
?>