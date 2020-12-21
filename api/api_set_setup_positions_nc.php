<?php
	
	$oPositions = new DBBase( $db_personnel, 'positions_nc' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SavePositionNC( $aParams );
		break;
		
		default : 
			loadPositionNC( $aParams['id'] );
		break;
	}
	
	function SavePositionNC( $aParams )
	{
		global $oPositions, $oResponse;
		
		$nCode = !empty( $aParams['cipher'] ) && is_numeric( $aParams['cipher'] ) ? $aParams['cipher'] : 0;
		 
		if( empty( $nCode ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето шифър!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//Проверка за повторение в кода
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id 	!= {$aParams['id']} ";
		$aWhere[] = " cipher = $nCode ";
		$aWhere[] = " to_arc = 0 ";  
		
		if( $nResult = $oPositions->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		if( !empty( $aDuplicate ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този шифър!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето длъжност!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		$aPosition = array();
		$aPosition['id'] 			= $aParams['id'];
		$aPosition['cipher']	 	= $aParams['cipher'];
		$aPosition['name'] 			= $aParams['name'];
		$aPosition['min_salary'] 	= $aParams['min_salary'];
		
		if( $nResult = $oPositions->update( $aPosition ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadPositionNC( $nID )
	{
		global $oPositions, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oPositions->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement('form1', 'cipher', 		array(), $aData['cipher']);
			$oResponse->setFormElement('form1', 'name',		 	array(), $aData['name']);
			$oResponse->setFormElement('form1', 'min_salary', 	array(), $aData['min_salary']);

			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>