<?php

	$oPositions = new DBBase( $db_personnel, 'positions' );

	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('edit_positions', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}
	
	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				$oDBPersonnel	= new DBPersonnel();
			    $nCount			= $oDBPersonnel->getCountPersonPositionByID($nID);
			    if (empty($nCount)) {
					if( $nReseul = $oPositions->toARC( $nID ) != DBAPI_ERR_SUCCESS )
						$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
			    }	else if ($nCount==1) $oResponse->setError(DBAPI_ERR_ALERT, "Не може да премахнете тази длъжност! \nНа тази длъжност има назначен {$nCount} човек!");
			 	else $oResponse->setError(DBAPI_ERR_ALERT, "Не може да премахнете тази длъжност! \nНа тази длъжност има назначени {$nCount} човека!");
				
				$aParams['api_action'] = 'result';
			break;
		
		default: 
			break;
	}
	
	class MyHandler extends APIHandler
	{
		function setFields( $aParams )
		{
			global $oResponse;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('setup_positions_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$oResponse->setField( 'code', 	'код', 			'Сортирай по код' );
			$oResponse->setField( 'name', 	'длъжност', 	'Сортирай по длъжност' );
			if($right_edit)
			{
				$oResponse->setField( '', 		'', 			'', 'images/cancel.gif', 'deletePosition', '');
				$oResponse->setFieldLink( 'name', 'viewPosition' );
			}
		}
		
		function getReport( $aParams )
		{
			$aWhere = array();
			if( isset( $aParams['sFunction'] ) && $aParams['sFunction'] != 'none' )
			{
				$aWhere[] = sprintf( " t.function = '%s'", addslashes( $aParams['sFunction'] ) );
			}
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
	
	$oHandler = new MyHandler( $oPositions, 'name', 'positions', 'Длъжности' );
	$oHandler->Handler( $aParams );

?>