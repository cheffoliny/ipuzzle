<?php
	
	$oPositions = new DBBase( $db_personnel, 'positions' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SavePosition( $aParams );
		break;
		
		default : 
			loadPosition( $aParams['id'] );
		break;
	}
	
	function SavePosition( $aParams )
	{
		global $oPositions, $oResponse;
		
		$nCode = !empty( $aParams['code'] ) && is_numeric( $aParams['code'] ) ? $aParams['code'] : 0;
		 
		if( empty( $nCode ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//Проверка за повторение в кода
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = $nCode ";
		
		if( $nResult = $oPositions->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
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
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето длъжност!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		$aPosition = array();
		$aPosition['id'] 		= $aParams['id'];
		$aPosition['code'] 		= $aParams['code'];
		$aPosition['function'] 	= $aParams['sFunction'];
		$aPosition['name'] 		= $aParams['name'];
		
		if( $nResult = $oPositions->update( $aPosition ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadPosition( $nID )
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
			
			$oResponse->setFormElement('form1', 'code', 		array(), $aData['code']);
			$oResponse->setFormElement('form1', 'sFunction', 	array(), $aData['function']);
			$oResponse->setFormElement('form1', 'name', 		array(), $aData['name']);

			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>