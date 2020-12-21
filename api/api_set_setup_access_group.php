<?php		

	$oResponse = new DBResponse();
	$oAccess = new DBAccess();
	
	switch ($aParams['api_action'])
	{
		
		case "update" : 
			if( empty($aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Задажително е да се въведе наименование на нивото за достъп !" );
				print( $oResponse->toXML() );
				break;
			}
			if( $oAccess->DublicateGroup($aParams['id'], $aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не се допуска дублиране на имена за групи!" );
				print( $oResponse->toXML() );
				break;
			}
			$oAccess->updateGroup($aParams);

		default : 
			if( ($nLevelOnce = $oAccess->getGroupOnce($aParams['id'], $aLevel)) == DBAPI_ERR_SUCCESS )
			{
				$oResponse->setFormElement('form1', 'name', array(), $aLevel['name']);
			}

			$oResponse->setError( $nLevelOnce );
			print( $oResponse->toXML() );
		break;
	}

?>