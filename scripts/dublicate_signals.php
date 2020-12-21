<?php
	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
		
	require_once ("../config/function.autoload.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	
	$aScheme = array();
	$oScheme = new DBTechRepeatSignalSchemes();
	$oReason = new DBHoldupReasons();
	$oObject = new DBObjectDuty();
	$aScheme = $oScheme->getActiveSchemes();
	$nIDReason = $oReason->getTechReason();
	//debug($aScheme);
	
	foreach ( $aScheme as &$val ) {
		$nTime = !empty($val['period']) ? $val['period'] : 2;
		$type = !empty($val['type']) ? $val['type'] : 'person';
		$sName = $val['name'];
		$nTimeDublicate = !empty($val['repeat_time']) ? $val['repeat_time'] : -1;

		$nIDSignal = isset($val['id_signal']) ? $val['id_signal'] : 0;
		$nIDSignal2 = isset($val['id_signal2']) ? $val['id_signal2'] : 0;
		$nIDSignal3 = isset($val['id_signal3']) ? $val['id_signal3'] : 0;

		$rest = isset($val['rest']) ? $val['rest'] : 0;
		$rest2 = isset($val['rest2']) ? $val['rest2'] : 0;
		$rest3 = isset($val['rest3']) ? $val['rest3'] : 0;

		$table1 = date("Y_m");
		$table2 = date( "Y_m", mktime(0, 0, 0, date("m"), date("d") - $nTime, date("Y")) );
		
		if ( !empty($nIDSignal) ) {	
			if ( $rest == 1 ) {
				$wh1 = "17,18,18,19,20,21,22,23,24,57,66,59,60,55";
			} else $wh1 = "1,2,3,4,5,6,7,8,51,52,55,58";
		} else $wh1 = "-1";

		if ( !empty($nIDSignal2) ) {	
			if ( $rest2 == 1 ) {
				$wh2 = "17,18,18,19,20,21,22,23,24,57,66,59,60,55";
			} else $wh2 = "1,2,3,4,5,6,7,8,51,52,55,58";
		} else $wh2 = "-1";
		
		if ( !empty($nIDSignal3) ) {	
			if ( $rest3 == 1 ) {
				$wh3 = "17,18,18,19,20,21,22,23,24,57,66,59,60,55";
			} else $wh3 = "1,2,3,4,5,6,7,8,51,52,55,58";
		} else $wh3 = "-1";

		if ( $table1 == $table2 ) { // Периода влиза в областта на една архивна таблица
			
			$sQuery = "
				SELECT 
					o.id_obj as id,
					m.id_sig,
					a.status,						
					UNIX_TIMESTAMP(a.msgTime) as msgTime
				FROM objects o
				LEFT JOIN messages m ON o.id_obj = m.id_obj
				LEFT JOIN `{$table1}` a ON a.id_msg = m.id_msg 
				WHERE 1
					AND m.id_msg IS NOT NULL

					AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - ({$nTime} * 86400) ) 
					AND (
						( m.id_sig = {$nIDSignal} AND a.status IN ({$wh1}) ) 
						OR ( m.id_sig = {$nIDSignal2} AND a.status IN ({$wh2}) ) 
						OR ( m.id_sig = {$nIDSignal3} AND a.status IN ({$wh3}) ) 
					)					
				ORDER BY o.id_obj, m.id_sig ASC
			";
					
		} else {  // Периода влиза в областта на повече от една архивна таблица
		
			$sQuery = "
				( SELECT 
					o.id_obj as id,
					m.id_sig,
					a.status,						
					UNIX_TIMESTAMP(a.msgTime) as msgTime
				FROM `{$table2}` a
				LEFT JOIN messages m ON m.id_msg = a.id_msg
				LEFT JOIN objects o ON o.id_obj = m.id_obj
				LEFT JOIN regions r ON r.id_region = o.id_region
				WHERE 1
					AND o.id_status = 1
					AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - (2 * 86400) ) 
					AND (
						( m.id_sig = {$nIDSignal} AND a.status IN ({$wh1}) ) 
						OR ( m.id_sig = {$nIDSignal2} AND a.status IN ({$wh2}) ) 
						OR ( m.id_sig = {$nIDSignal3} AND a.status IN ({$wh3}) ) 
					)					
				ORDER BY o.id_obj, m.id_sig ASC )

				UNION 

				( SELECT 
					o.id_obj as id,
					m.id_sig,
					a.status,						
					UNIX_TIMESTAMP(a.msgTime) as msgTime
				FROM `{$table1}` a
				LEFT JOIN messages m ON m.id_msg = a.id_msg
				LEFT JOIN objects o ON o.id_obj = m.id_obj
				WHERE 1
					AND o.id_status = 1
					AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - (2 * 86400) ) 
					AND (
						( m.id_sig = {$nIDSignal} AND a.status NOT IN ({$wh1}) ) 
						OR ( m.id_sig = {$nIDSignal2} AND a.status NOT IN ({$wh2}) ) 
						OR ( m.id_sig = {$nIDSignal3} AND a.status NOT IN ({$wh3}) ) 
					)					
				ORDER BY o.id_obj, m.id_sig ASC )
			";		
		}

		$rs = $db_telepol->Execute( $sQuery );
		
		if( $rs ) {
			if ( !$rs->EOF ) {
				$aDataRes = $rs->getArray();
			}
		}
		
		//debug($tmpArr);
		$tmpArr = array();
		
		foreach ( $aDataRes as $value ) {
			$nID = $value['id'];

			if ( !isset($tmpArr[$value['id']]) ) {
				$tmpArr[$value['id']] = $value;
			} else {
				$k = ($value['msgTime'] - $tmpArr[$value['id']]['msgTime']);

				if ( (($k > 0) && ($k <= $nTimeDublicate)) && ($value['status'] == $tmpArr[$value['id']]['status']) && ($value['id_sig'] == $tmpArr[$value['id']]['id_sig']) ) {
					$nIDTmp = $oObject->getObjectNew( $nID );
					if ( isset($nIDTmp['id']) && $nIDTmp['id'] > 0 ) {
						$nID = $nIDTmp['id'];
						
						if ( $type == "problem" ) {
							
							$qry = "
								INSERT INTO object_troubles
									(id_obj, id_problem, problem_date, problem_info, to_arc) 
								VALUES
									('{$nID}', 0, NOW(), CONCAT( 'Автоматичен: Дублиране на сигнали от шаблон: {$sName} \nдата: ', DATE_FORMAT(NOW(), '%d.%m.%Y %H:%i:%s') ), 0)
							";

						} else {
						
							$qry = "
								INSERT INTO tech_requests
									(id_object, id_limit_card, id_contract, type, tech_type, note, created_type, created_time, updated_time, to_arc ) 
								VALUES
									('{$nID}', 0, 0, 'holdup', 'no_signals', '{$nIDReason}', CONCAT( 'Автоматична: Дублиране на сигнали от шаблон: {$sName} \nдата: ', DATE_FORMAT(NOW(), '%d.%m.%Y %H:%i:%s') ), 'automatic', NOW(), NOW(), 0)
							";
						
						}				
						
						$rss = $db_sod->Execute( $qry );
						
					}
					
					unset($tmpArr[$value['id']]);
				} else $tmpArr[$value['id']] = $value;
			}
			
		}
	}
	
?>