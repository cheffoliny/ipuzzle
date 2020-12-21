<?php
	$oActives = New DBBase( $db_storage, 'actives' );

	switch( $aParams['api_action']) {
		case "save" :
			SaveDocType( $aParams );
		break;
		default :
			loadDocType( $aParams['id'] );
		break;
	}
	
	function SaveDocType( $aParams ) {
		global $oActives, $oResponse;
		
		if( empty( $aParams['code'] ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['name'] ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето наименование!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = '{$aParams['code']}' ";
		
		if( $nResult = $oActives->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		
		if( !empty( $aDuplicate ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
	
		$aActives = array();
		$aActives['id'] 		= $aParams['id'];
		$aActives['code'] 		= $aParams['code'];
		$aActives['name'] 		= $aParams['name'];
		
		if( $nResult = $oActives->update( $aActives ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadDocType( $nID ) {
		global $oActives, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 ) {
			// Редакция
			$aData = array();
			
			if( $nResult = $oActives->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement('form1', 'code', array(), $aData['code']);
			$oResponse->setFormElement('form1', 'name', array(), $aData['name']);

			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );
?>