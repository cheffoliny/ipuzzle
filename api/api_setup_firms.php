<?php

	$oFirm = New DBBase( $db_sod, 'firms' );

	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('firms_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}
	
	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				if( $nReseul = $oFirm->toARC( $nID ) != DBAPI_ERR_SUCCESS )
					$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
				
				$aParams['api_action'] = 'result';
			break;
		default: 
			break;
	}

	class MyHandler extends APIHandler
	{
		function setFields( $aParams )
		{
			global $oResponse, $right_edit;
			
			$oResponse->setField( 'code' , 			'код', 							'Сортирай по код', NULL, NULL, NULL, array('DATA_FORMAT' => DF_NUMBER) );
			$oResponse->setField( 'name', 			'наименование', 		'Сортирай по наименование' );
			$oResponse->setField( 'mol', 				'мол', 							'Сортирай по мол' );
			$oResponse->setField( 'jur_name', 	'юридическо лице',	'Сортирай по юридическо лице' );
			if( $right_edit )
			{
				$oResponse->setField( '',			'',					'', 'images/cancel.gif', 'deleteFirm', '');
				$oResponse->setFieldLink( 'name', 'viewFirm' );
			}
		}
		
		function getReport( $aParams )
		{
			$aWhere = array();
			$aWhere[] = " t.to_arc = 0 ";
			
			$sQuery = sprintf("
				SELECT 
					SQL_CALC_FOUND_ROWS 
					t.id as _id,
					t.id,
					t.code,
					t.name,
					t.mol,
					t.jur_name 
				FROM 
					%s t
				",
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
	}
	
	$oHandler = new MyHandler( $oFirm, 'name', 'firms', 'Фирми' );
	$oHandler->Handler( $aParams );

?>