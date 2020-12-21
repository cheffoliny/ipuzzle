<?php
	
	$oDocType = new DBBase( $db_personnel, 'document_types' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SaveDocType( $aParams );
		break;
		
		default : 
			loadDocType( $aParams['id'] );
		break;
	}
	
	function SaveDocType( $aParams )
	{
		global $oDocType, $oResponse;
		
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето наименование!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		$aDocType = array();
		$aDocType['id'] 		= $aParams['id'];
		$aDocType['name'] 		= $aParams['name'];
		
		if( $nResult = $oDocType->update( $aDocType ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadDocType( $nID )
	{
		global $oDocType, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oDocType->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement('form1', 'name', array(), $aData['name']);

			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>