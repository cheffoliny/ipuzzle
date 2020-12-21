<?php

	$oResponse = new DBResponse();
	$oAccess = new DBAccess();
	
	switch ( $aParams['api_action'] )
	{
		case "update" : 
			$db_system->debug=true;
			
			if( empty($aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е въведено наименование на профила !" );
				print( $oResponse->toXML() );
				break;
			}
			if( $oAccess->DublicateProfile($aParams['id'], $aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не се допуска дублиране на имена за потребителски профили !" );
				print( $oResponse->toXML() );
				break;
			}
			
			if($aParams['save_like_new'] == 'yes') {
				$aParams['id'] = 0;
			}
			$oAccess->updateProfile($aParams);
			print( $oResponse->toXML() );
		break;

		default : 
			if( ($nProfileOnce = $oAccess->getProfileOnce($aParams['id'], $aProfile)) == DBAPI_ERR_SUCCESS )
			{
				$oResponse->setFormElement('form1', 'name', array(), $aProfile['name']);
				$oResponse->setFormElement('form1', 'description', array(), $aProfile['description']);
				if( $aProfile['is_default'] )
					$oResponse->setFormElement('form1', 'is_default', array("checked"=>'checked'),'' );

				$aProfileLevels=array();
				$all_levels=false;
				$oAccess->getProfileLevels( $aParams['id'], $all_levels, $aProfileLevels );
				foreach($aProfileLevels as $value)
				{
					$oResponse->setFormElement('form1', 'level_'.$value['id_level'], array("checked"=>'checked'),'');
				}
				if( $all_levels )
					$oResponse->setFormElement('form1', 'all_levels', array("checked"=>'checked'),'');
			}


			$oResponse->setError( $nProfileOnce );
			print( $oResponse->toXML() );
		break;
	}

?>