<?php

	$oSalaryExpense = New DBBase( $db_personnel, 'salary_expense_types' );

	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('edit_salary_expense', $_SESSION['userdata']['access_right_levels'])) 
		{
			$right_edit = true;
		}

	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				if( $nReseul = $oSalaryExpense->toARC( $nID ) != DBAPI_ERR_SUCCESS )
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
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('salary_expense_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$oResponse->setField( 'code', 		'код на отчисление', 	'Сортирай по код на отчисление' );
			$oResponse->setField( 'name', 		'наименование', 		'Сортирай по наименование' );
			$oResponse->setField( 'measure', 	'мерна единица', 		'Сортирай по мерна единица' );
			$oResponse->setField( 'source', 	'източник', 		'Сортирай по източник' );
			if($right_edit)
			{
				$oResponse->setField( '',			'',						'', 'images/cancel.gif', 'deleteSalaryExpense', '');
				$oResponse->setFieldLink( 'name', 'viewSalaryExpense' );
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
						WHEN 'limit_card' THEN 'лимитна карта'
						WHEN 'schedule' THEN 'работен график'
						WHEN 'work_card' THEN 'работна карта'
						ELSE ''
					END AS source
				FROM 
					%s t 
					LEFT JOIN {$db_name_storage}.measures m ON m.id = t.measure
				", 
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
			
	}
	
	$oHandler = new MyHandler( $oSalaryExpense, 'name', 'salary_expense', 'Отчисления' );
	$oHandler->Handler( $aParams );
	
?>