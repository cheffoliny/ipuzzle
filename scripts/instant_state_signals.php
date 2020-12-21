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
	$oScheme = new DBTechInstantStateSchemes();
	$oReason = new DBHoldupReasons();
	$oObject = new DBObjectDuty();
	$aScheme = $oScheme->getActiveSchemes();
	$nIDReason = $oReason->getTechReason();
	//debug($aScheme);
	
	foreach ( $aScheme as &$val ) {
		$type = !empty($val['type']) ? $val['type'] : "person";
		$sName = $val['name'];
		$signals = isset($val['signals']) ? $val['signals'] : "-1";

		$sQuery = "
			SELECT
				o.id_obj as id
			FROM messages m
			LEFT JOIN objects o ON o.id_obj = m.id_obj
			WHERE 1
				AND m.id_msg IS NOT NULL
				AND o.id_status = 1
				AND m.id_sig IN ({$signals})
				AND m.flag = 1
			GROUP BY o.id_obj
		";
			
		$rs = $db_telepol->Execute( $sQuery );
		
		if( $rs ) {
			if ( !$rs->EOF ) {
				$aDataRes = $rs->getArray();
			}
		}
		
		foreach ( $aDataRes as $value ) {
			$nID = $value['id'];
			$nIDTmp = $oObject->getObjectNew( $nID );
			if ( isset($nIDTmp['id']) && $nIDTmp['id'] > 0 ) {
				$nID = $nIDTmp['id'];
			
				if ( $type == "problem" ) {
					
					$qry = "
						INSERT INTO object_troubles
							(id_obj, id_problem, problem_date, problem_info, to_arc) 
						VALUES
							('{$nID}', 0, NOW(), CONCAT( 'Автоматичен: Моментно състояние от шаблон: {$sName} \nдата: ', DATE_FORMAT(NOW(), '%d.%m.%Y %H:%i:%s') ), 0)
					";

				} else {
				
					$qry = "
						INSERT INTO tech_requests
							(id_object, id_limit_card, id_contract, type, tech_type, note, created_type, created_time, updated_time, to_arc ) 
						VALUES
							('{$nID}', 0, 0, 'holdup', 'no_signals', '{$nIDReason}', CONCAT( 'Автоматична: Моментно състояние от шаблон: {$sName} \nдата: ', DATE_FORMAT(NOW(), '%d.%m.%Y %H:%i:%s') ), 'automatic', NOW(), NOW(), 0)
					";
				
				}				
				 
				$rss = $db_sod->Execute( $qry );
			}
		}
	}
	
?>