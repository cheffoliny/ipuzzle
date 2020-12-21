<?php
	
	$oSalaryExpense = 	new DBBase( $db_personnel, 'salary_expense_types' );
	$oMeasures = 		new DBBase( $db_storage, 'measures' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SaveSalaryExpense( $aParams );
		break;
		
		default : 
			loadSalaryExpense( $aParams['id'] );
		break;
	}
	
	function SaveSalaryExpense( $aParams )
	{
		global $oSalaryExpense, $oResponse;
		
		if( empty( $aParams['code'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		//Проверка за повторение в кода
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = '{$aParams['code']}' ";
		
		if( $nResult = $oSalaryExpense->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		if( !empty( $aDuplicate ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
				
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето наименование!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		if( empty( $aParams['measure'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето мерна единица!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		/*
		if( empty( $aParams['source'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето източник!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		*/
		
		$aSalaryExpense = array();
		$aSalaryExpense['id'] 		= $aParams['id'];
		$aSalaryExpense['code'] 	= $aParams['code'];
		$aSalaryExpense['name'] 	= $aParams['name'];
		$aSalaryExpense['measure'] 	= $aParams['measure'];
		$aSalaryExpense['source'] 	= $aParams['source'];
		
		$oSalaryExpense2 = new DBSalaryExpense();
		
		if(!empty($aParams['source'])) {
			$oSalaryExpense2->eraseSources($aParams['source']);
		}
		
		if( $nResult = $oSalaryExpense->update( $aSalaryExpense ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadSalaryExpense( $nID )
	{
		global $oSalaryExpense, $oMeasures, $oResponse, $db;
		
		$aMeasures = array();

		$id = (int) $nID;

		if( $id == 0 )
		{
			$oMeasures->getResult( $aMeasures );
			
			$oResponse->setFormElement( 'form1', 'measure' );
			foreach( $aMeasures as $aMeasure )
			{
				$oResponse->SetFormElementChild( 'form1', 'measure', array('value'=>$aMeasure['id']), sprintf("%s (%s)", $aMeasure['description'], $aMeasure['code']) );
			}
			$aSource = array();
			$aSource['work_card'] = "работна карта";
					
			$oResponse->setFormElement( 	'form1','source' );
			$oResponse->setFormElementChild('form1','source', array('value' => 0), '--Изберете--');	
			foreach( $aSource as $key => $value )
			{
				$oResponse->SetFormElementChild( 'form1', 'source', array('value'=>$key), $value  );
			}
		}
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oSalaryExpense->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement('form1', 'code', array(), $aData['code']);
			$oResponse->setFormElement('form1', 'name', array(), $aData['name']);

			$oMeasures->getResult( $aMeasures );
			$oResponse->setFormElement( 'form1', 'measure' );
			foreach( $aMeasures as $aMeasure )
			{
				$arr = array();
				if( $aMeasure['id'] == $aData['measure'] )
					$arr = array( 'selected'=>'selected' );
				
				$oResponse->SetFormElementChild( 'form1', 'measure', array_merge(array('value'=>$aMeasure['id']), $arr), sprintf("%s (%s)", $aMeasure['description'], $aMeasure['code']) );
			}
			
			$aSource = array();
			$aSource['work_card'] = "работна карта";
			
			$oSalaryExpense = new DBSalaryExpense();
			$aSalaryExpense = $oSalaryExpense -> getSourceByID($nID);
			
			$sSalaryExpense = $aSalaryExpense['source'];
						
			$oResponse->setFormElement( 	'form1','source' );
			$oResponse->setFormElementChild('form1','source', array('value' => 0), '--Изберете--');	
			foreach( $aSource as $key => $value )
			{
				if ( $sSalaryExpense == $key ) {
					$ch = array( "selected" => "selected" );
				} else $ch = array();
				$oResponse->SetFormElementChild( 'form1', 'source',array_merge(array('value'=>$key), $ch), $value  );
			}
			
			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>