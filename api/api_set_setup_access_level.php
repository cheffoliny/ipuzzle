<?php

	$oResponse = new DBResponse();
	$oAccess = new DBAccess();

	switch ($aParams['api_action'])
	{
		case "update" : 
			
			if( empty($aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е въведено наименование на нивото за достъп !" );
				print( $oResponse->toXML() );
				break;
			}
			if( $oAccess->DublicateLevel($aParams['id'], $aParams['name']) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не се допуска дублиране на имена за нива на достъп !" );
				print( $oResponse->toXML() );
				break;
			}
			$oAccess->updateLevel($aParams);
			print( $oResponse->toXML() );
		break;

		default : 
			$aLevel = array();
			$aGroups = array();
			$aAllFiles = array();
			$aLevelFiles = array();

			$oAccess->getLevelGroups( $aGroups );
			$oAccess->getFiles($aAllFiles);

			if( ($nLevelOnce = $oAccess->getLevelOnce($aParams['id'], $aLevel)) == DBAPI_ERR_SUCCESS )
			{
				if( !empty($aLevel['id_group']) )
				{
					$selected_group = $aLevel['id_group'];
				}
				else
				{
					$selected_group = !empty($aParams['group']) ? $aParams['group'] : 0 ;
				}
				
				$oResponse->setFormElement('form1', 'id_group', array(), '');
				foreach($aGroups as $value)
				{
					$selected = $selected_group == $value['id'] ? array('selected'=>'selected') : array();
					$oResponse->setFormElementChild('form1', 'id_group', array_merge(array("value"=>$value['id']), $selected), $value['name']);
				}

				$oResponse->setFormElement('form1', 'name', array(), $aLevel['name']);
				$oResponse->setFormElement('form1', 'description', array(), $aLevel['description']);
				
				$oAccess->getLevelFiles($aParams['id'],$aLevelFiles);
				$oResponse->setFormElement('form1', 'level_files', array(), '');
				foreach($aLevelFiles as $value)
				{
					if( isset($aAllFiles[$value['filename']]) ) unset( $aAllFiles[$value['filename']] );
					$oResponse->setFormElementChild('form1', 'level_files', array_merge(array("value"=>$value['filename'])), $value['filename']);
				}

				$oResponse->setFormElement('form1', 'all_files', array(), '');
				foreach($aAllFiles as $value)
				{
					$oResponse->setFormElementChild('form1', 'all_files', array_merge(array("value"=>$value)), $value);
				}
			}

			$oResponse->setError( $nLevelOnce );
			print( $oResponse->toXML() );
		break;
	}

?>