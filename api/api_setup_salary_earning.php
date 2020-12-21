<?php

	$oSalaryEarning = New DBBase( $db_personnel, 'salary_earning_types' );
	
	$right_edit = false;
	if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
		if( in_array( 'edit_salary_earning', $_SESSION['userdata']['access_right_levels'] ) )
		{
			$right_edit = true;
		}
	
	switch( $aParams['api_action'] )
	{
		case 'delete':
				$nID = (int) $aParams['id'];
				if( $nReseul = $oSalaryEarning->toARC( $nID ) != DBAPI_ERR_SUCCESS )
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
			global $oResponse;
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'salary_earning_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$oResponse->setField( "code", 				"код на наработка", 	"Сортирай по код на наработка" );
			$oResponse->setField( "name", 				"наименование", 		"Сортирай по наименование" );
			$oResponse->setField( "measure", 			"мерна единица", 		"Сортирай по мерна единица" );
			$oResponse->setField( "source", 			"източник", 			"Сортирай по източник" );
			$oResponse->setField( "leave_type", 		"при отпуски", 			"Сортирай" );
			$oResponse->setField( "is_compensation", 	"обезщетение", 			"Сортирай", "images/confirm.gif", NULL, NULL, array( "style" => "width: 100px;", "DATA_FORMAT" => DF_CENTER ) );
			$oResponse->setField( "is_hospital", 		"болнични", 			"Сортирай", "images/confirm.gif", NULL, NULL, array( "style" => "width: 100px;", "DATA_FORMAT" => DF_CENTER ) );
			
			if( $right_edit )
			{
				$oResponse->setField( '',			'',						'', 'images/cancel.gif', 'deleteSalaryEarning', '');
				$oResponse->setFieldLink( 'name', 'viewSalaryEarning' );
			}
		}
		
		function getReport( $aParams )
		{
			global $db_name_storage;
			
			$aWhere = array();
			$aWhere[] = " t.to_arc = 0 ";
			
			$sQuery = sprintf(" 
				SELECT 
					SQL_CALC_FOUND_ROWS 
					t.id as _id, 
					t.id, 
					t.code,
					t.name,
					m.code as measure,
					CASE source
						WHEN 'limit_card' 	 THEN 'лимитна карта'
						WHEN 'schedule' 	 THEN 'работен график'
						WHEN 'work_card' 	 THEN 'работна карта'
						WHEN 'asset_earning' THEN 'наработка актив'
						WHEN 'asset_own'	 THEN 'актив самоучастие'
						WHEN 'asset_waste'	 THEN 'бракуване на актив'
					END AS source,
					CASE t.leave_type
						WHEN 'none' THEN ''
						WHEN 'due' THEN 'Платен'
						WHEN 'unpaid' THEN 'Неплатен'
						ELSE ''
					END AS leave_type,
					t.is_compensation,
					t.is_hospital
				FROM 
					%s t 
					LEFT JOIN {$db_name_storage}.measures m ON m.id = t.measure
				", 
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
	}
	
	$oHandler = new MyHandler( $oSalaryEarning, 'name', 'salary_earning', 'Наработки' );
	$oHandler->Handler( $aParams );

?>