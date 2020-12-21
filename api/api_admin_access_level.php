<?php

	$oResponse = new DBResponse();
	$oAccess = new DBAccess();

	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}

	switch ($aParams['api_action'])
	{
		case 'delete_level' : 
			if( !empty( $aParams['id'] ) )
				$oAccess->deleteLevel( $aParams['id'] );
			break;

		case 'delete_group' : 
			if( !empty( $aParams['id'] ) )
			{
				$oAccess->deleteGroup( $aParams['id'] );
				$aParams['level_group']=0;
			}
			break;
	}

	$aGroups = array();
	$oAccess->getLevelGroups( $aGroups );
	$oResponse->setFormElement('form1', 'level_group', array(), '');
	$oResponse->setFormElementChild('form1', 'level_group', array("value"=>0), "--- Всички ---");
	$selected_group = !empty($aParams['level_group']) ? $aParams['level_group'] : 0;
	
	foreach($aGroups as $value)
	{
		$selected = $selected_group == $value['id'] ? array('selected'=>'selected') : array();
		$oResponse->setFormElementChild('form1', 'level_group', array_merge(array("value"=>$value['id']), $selected), $value['name']);
	}

	if( empty( $aParams['sfield'] ) )
		$aParams['sfield'] = "group_name";

	if( empty( $aParams['stype'] ) )
		$aParams['stype'] = DBAPI_SORT_ASC;

	if( empty( $aParams['current_page'] ) )
		$aParams['current_page'] = "1";

	if( $aParams['api_action'] == "export_to_xls" || $aParams['api_action'] == "export_to_pdf" )
	{
		$aParams['current_page'] = "0";
	}

	if( ($nResult = $oAccess->getLevelsResult(
		$selected_group, 
		$aParams['sfield'], 
		$aParams['stype'], 
		$aParams['current_page'], 
		$oResponse) ) != DBAPI_ERR_SUCCESS )
	{
		$oResponse->setError( $nResult );
	}
	else
	{
		$oResponse->setField(	'name', 
								'наименование', 
								'Сортиране по наименование', 
								NULL, 
								$right_edit ? 'level_edit' : NULL, 
								NULL, 
								NULL
		);

		$oResponse->setField(	'group_name', 
								'Група', 
								'Сортиране по група', 
								NULL, 
								NULL, 
								NULL, 
								NULL
		);

		$oResponse->setField(	'description', 
								'Описание', 
								'Сортиране по описание', 
								NULL, 
								NULL, 
								NULL, 
								NULL
		);

		$oResponse->setField(	'filenames', 
								'Файлове', 
								'Сортиране по имена на файлове', 
								NULL, 
								NULL, 
								NULL, 
								NULL
		);

		if ($right_edit)
		{
			$oResponse->setField(	'btn_delete', 
									'', 
									NULL, 
									'images/cancel.gif', 
									'level_delete', 
									'', 
									NULL
			);
		}
	}

	if ( $aParams['api_action'] == "export_to_xls" )
	{
		$oResponse->toXLS("access_levels_".date('y_m_d').".xls", "Нива на достъп");
	}
	elseif ( $aParams['api_action'] == "export_to_pdf" )
	{
		$oResponse->toPDF("Нива на достъп", 'L');
	}
	else
	{
		print( $oResponse->toXML() );
	}

?>