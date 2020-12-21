<?php
	
	$oSalaryEarning = 	new DBBase( $db_personnel, 'salary_earning_types' );
	$oMeasures = 		new DBBase( $db_storage, 'measures' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SaveSalaryEarning( $aParams );
		break;
		
		default : 
			loadSalaryEarning( $aParams['id'] );
		break;
	}
	
	function resetLeaveTypeMarker( $sLeaveType = "due" )
	{
		global $db_personnel;
		
		$sQuery = "
			UPDATE
				salary_earning_types
			SET
				leave_type = 'none'
			WHERE
				leave_type = '{$sLeaveType}'
		";
		
		$oRS = $db_personnel->Execute( $sQuery );
		
		if( !$oRS )
		{
			return DBAPI_ERR_SQL_QUERY;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function resetIsCompensationMarker()
	{
		global $db_personnel;
		
		$sQuery = "
			UPDATE
				salary_earning_types
			SET
				is_compensation = 0
			WHERE
				is_compensation = 1
		";
		
		$oRS = $db_personnel->Execute( $sQuery );
		
		if( !$oRS )
		{
			return DBAPI_ERR_SQL_QUERY;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function resetIsHospitalMarker()
	{
		global $db_personnel;
		
		$sQuery = "
			UPDATE
				salary_earning_types
			SET
				is_hospital = 0
			WHERE
				is_hospital = 1
		";
		
		$oRS = $db_personnel->Execute( $sQuery );
		
		if( !$oRS )
		{
			return DBAPI_ERR_SQL_QUERY;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function SaveSalaryEarning( $aParams )
	{
		global $oSalaryEarning, $oResponse;
		
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
		
				//throw new Exception("reag");
		
		if( $nResult = $oSalaryEarning->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
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
		
		if( $aParams['sLeaveType'] != "none" )
		{
			if( resetLeaveTypeMarker( $aParams['sLeaveType'] ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
		}
		
		if( !empty( $aParams['nIsCompensation'] ) )
		{
			if( resetIsCompensationMarker() != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
		}
		
		if( !empty( $aParams['nIsHospital'] ) )
		{
			if( resetIsHospitalMarker() != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__ );
				return DBAPI_ERR_SQL_QUERY;
			}
		}
		
		$aSalaryEarning = array();
		$aSalaryEarning['id'] 				= $aParams['id'];
		$aSalaryEarning['code'] 			= $aParams['code'];
		$aSalaryEarning['name'] 			= $aParams['name'];
		$aSalaryEarning['measure'] 			= $aParams['measure'];
		$aSalaryEarning['source'] 			= $aParams['source'];
		$aSalaryEarning['leave_type'] 		= $aParams['sLeaveType'];
		$aSalaryEarning['is_compensation'] 	= $aParams['nIsCompensation'];
		$aSalaryEarning['is_hospital'] 		= $aParams['nIsHospital'];
		
		$oSalaryEarning2 = new DBSalaryEarning();
		
		if(!empty($aParams['source'])) {
			$oSalaryEarning2->eraseSources($aParams['source']);
		}
		
		if( $nResult = $oSalaryEarning->update( $aSalaryEarning ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadSalaryEarning( $nID )
	{
		global $oSalaryEarning, $oMeasures, $oResponse, $db;
		
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
			$aSource['limit_card'] 		= "лимитна карта";
			$aSource['work_card'] 		= "работна карта";
			$aSource['schedule'] 		= "работен график";
			$aSource['asset_earning'] 	= "наработка актив";
			$aSource['asset_own'] 		= "актив самоучастие";
			$aSource['asset_waste'] 	= "бракуване на актив"; 
			
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
			
			if( $nResult = $oSalaryEarning->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
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
			$aSource['limit_card'] 		= "лимитна карта";
			$aSource['work_card'] 		= "работна карта";
			$aSource['schedule'] 		= "работен график";
			$aSource['asset_earning'] 	= "наработка актив";
			$aSource['asset_own'] 		= "актив самоучастие";
			$aSource['asset_waste'] 	= "бракуване на актив"; 
			
			$oSalaryEarning = new DBSalaryEarning();
			$aSalaryEarning = $oSalaryEarning -> getSourceByID($nID);
			
			$sSalaryEarning = $aSalaryEarning['source'];
						
			$oResponse->setFormElement( 	'form1','source' );
			$oResponse->setFormElementChild('form1','source', array('value' => 0), '--Изберете--');	
			foreach( $aSource as $key => $value )
			{
				if ( $sSalaryEarning == $key ) {
					$ch = array( "selected" => "selected" );
				} else $ch = array();
				$oResponse->SetFormElementChild( 'form1', 'source',array_merge(array('value'=>$key), $ch), $value  );
			}
			
			$oResponse->setFormElementAttribute( "form1", "sLeaveType", "value", $aData['leave_type'] );
			
			if( !empty( $aData['is_compensation'] ) )
			{
				$oResponse->setFormElement( "form1", "nIsCompensation", array( "checked" => "checked" ) );
			}
			else
			{
				$oResponse->setFormElement( "form1", "nIsCompensation", array( "checked" => "" ) );
			}
			
			if( !empty( $aData['is_hospital'] ) )
			{
				$oResponse->setFormElement( "form1", "nIsHospital", array( "checked" => "checked" ) );
			}
			else
			{
				$oResponse->setFormElement( "form1", "nIsHospital", array( "checked" => "" ) );
			}
			//debug($aData);
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>