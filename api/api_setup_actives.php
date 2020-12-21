<?php
	$oActives = New DBBase( $db_storage, 'actives' );

	switch($aParams['api_action']) {
		case 'delete' : 
				$nID = (int) $aParams['id'];
				if( $nReseul = $oActives->toARC( $nID ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
				}
				$aParams['api_action'] = 'result';
			break;
		default:
			break;
	}
	
	class MyHandler extends APIHandler {
		function setFields( $aParams ) {
			global $oResponse;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('actives_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$oResponse->setField( 'code', 		'код',			'Сортирай по код' );
			$oResponse->setField( 'name', 		'наименование', 'Сортирай по наименование' );
			if ($right_edit) {
				$oResponse->setField( '', 			'', 			'', 'images/cancel.gif', 'deleteActives', '');
				$oResponse->setFieldLink( 'code', 'editActives' );
				$oResponse->setFieldLink( 'name', 'editActives' );
			}
		}
			
		function getReport( $aParams ) {
			$aWhere = array();
			$aWhere[] = " t.to_arc = 0 ";
			
			$sQuery = sprintf(" 
				SELECT 
					SQL_CALC_FOUND_ROWS 
					t.id as _id, 
					t.id,
					t.code, 
					t.name
				FROM 
					%s t 
				", 
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
			
	}
	
	$oHandler = new MyHandler( $oActives, 'name', 'actives', 'Типове документи' );
	$oHandler->Handler( $aParams );
?>