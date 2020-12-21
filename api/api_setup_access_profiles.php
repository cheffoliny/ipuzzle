<?php
	
	$oResponse = new DBResponse();
	$oAccess = new DBAccess();

	$right_edit = false;
	if ( !empty($_SESSION['userdata']['access_right_levels']) )
		if ( in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels']) )
		{
			$right_edit = true;
		}

	switch ( $aParams['api_action'] )
	{
		case 'delete' : 
			if( !empty( $aParams['id'] ) )
				$oAccess->deleteProfile( $aParams['id'] );
			break;
	}

	if( empty( $aParams['sfield'] ) )
		$aParams['sfield'] = "name";
		
	if( empty( $aParams['stype'] ) )
		$aParams['stype'] = DBAPI_SORT_ASC;

	if( empty( $aParams['current_page'] ) )
		$aParams['current_page'] = "1";

	if( $aParams['api_action'] == "export_to_xls" || $aParams['api_action'] == "export_to_pdf" )
	{
		$aParams['current_page'] = "0";
	}
	if( ($nResult = $oAccess->getProfileResult(
		$aParams['sfield'], 
		$aParams['stype'], 
		$aParams['current_page'], 
		$oResponse)) != DBAPI_ERR_SUCCESS )
	{
		$oResponse->setError( $nResult );
	}
	else
	{
		$oResponse->setField(	'name', 
								'наименование', 
								'Сортиране по наименование', 
								NULL, 
								$right_edit ? 'profile_edit' : NULL, 
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

		$oResponse->setField(	'count_users', 
								'Бр', 
								'Сортиране по брой потребители от профила', 
								NULL, 
								NULL, 
								NULL, 
								NULL
		);

		$oResponse->setField(	'is_default', 
								'по подразбиране', 
								'Сортиране по профил по подразбиране', 
								'images/confirm.gif', 
								NULL, 
								NULL, 
								NULL
		);

		$oResponse->setField(	'users', 
								'Потребители от профила', 
								'Сортиране по потребители от профила', 
								NULL, 
								'view_accounts', 
								NULL, 
								NULL
		);

		if ($right_edit)
		{
			$oResponse->setField(	'btn_delete', 
									'', 
									NULL, 
									'images/cancel.gif', 
									'profile_delete', 
									'', 
									NULL
			);
		}
	}

	if ( $aParams['api_action'] == "export_to_xls" )
	{
		$oResponse->toXLS("access_profile_".date('y_m_d').".xls", "Потребителски профили");
	}
	elseif ( $aParams['api_action'] == "export_to_pdf" )
	{
		$oResponse->toPDF("Нива на достъп",'P');
	}
	else
	{
		print( $oResponse->toXML() );
	}

?>