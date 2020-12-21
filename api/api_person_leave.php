<?php
	$oHospital = new DBLeaves();
	$oPerson = new DBPersonnel();
	$oDBPersonLeaves = new DBPersonLeaves();
	$oDBPersonLeavesNumbers = new DBPersonLeavesNumbers();
	
	$oLeave = New DBBase( $db_personnel, 'person_leaves' );
	
	$right_level = 'none';
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if (in_array('edit_personnel', $_SESSION['userdata']['access_right_levels'])) {
			$right_level = 'edit';
		} else $right_level = 'none';
	}

	switch ($aParams['api_action']) {
		case "delete":
			if ($aParams['idc'] > 0) {
				if ( ($nResult = $oHospital->delete( $aParams['idc']) ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult );
				}
			}
			print( $oResponse->toXML() );
		break;
		
		case "save":
			
			$sUpdateQuery = "
				UPDATE
					personnel
				SET
					is_substitute_needed = {$aParams['nIsSubstituteNeeded']}
				WHERE
					id = {$aParams['id']}
			";
			
			$oRS = $db_personnel->Execute( $sUpdateQuery );
			if( !$oRS )$oResponse->setError( DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__ );
			
			print( $oResponse->toXML() );
			
			break;
		
		default:
			
			//Automatic Add Year Leave
			$nYear = date( "Y" );
			
			$aDuplicate = array();
			$aWhere = array();
			$aWhere[] = " to_arc = 0 ";
			$aWhere[] = " id_person = {$aParams['id']} ";
			$aWhere[] = " year = '{$nYear}' ";
			$aWhere[] = " type = 'leave' ";
			$aWhere[] = " leave_types = 'due' ";
			
			if( $nResult = $oLeave->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				break;
			}
			
			if( empty( $aDuplicate ) )
			{
				$aLeave = array();
				$aLeave['id'] 			= 0;
				$aLeave['id_person'] 	= $aParams['id'];
				$aLeave['year'] 		= $nYear;
				$aLeave['due_days'] 	= $oDBPersonLeaves->getHowManyDaysToAdd( $aLeave['id_person'] );
				$aLeave['type'] 		= "leave";
				$aLeave['leave_types'] 	= "due";
				
				if( $nResult = $oLeave->update( $aLeave ) != DBAPI_ERR_SUCCESS )
				{
					$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
					break;
				}
			}
			//End Automatic Add Year Leave
			
			//Automatic Leave Numbers
			$oDBPersonLeavesNumbers->checkNewYear();
			//End Automatic Leave Numbers
			
			if (empty($aParams['sfield']))
				$aParams['sfield'] = 'year';
				
			if (empty($aParams['stype']))
				$aParams['stype'] = DBAPI_SORT_ASC;
				
			if (empty($aParams['current_page']))
				$aParams['current_page'] = "1";
			
			if ( ($nResult = $oHospital->getResult($aParams['id'], $aParams['sfield'], $aParams['stype'], $aParams['current_page'], $oResponse)) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult );
			} else {
				$oResponse->setField( 'year', 				'година', 					'Сортирай по година' );
				$oResponse->setField( 'due_days', 			'полагаеми дни', 			'Сортирай по дни' );
				$oResponse->setField( 'due_days_all', 		'полаг. общо', 				'Сортирай по дни' );
				$oResponse->setField( 'used_days',			'използвани дни',			'Сортирай по дни' );
				$oResponse->setField( 'used_extra_days',	'изп. допълн. дни',			'Сортирай по дни' );
				$oResponse->setField( 'remain',				'оставащи дни', 			'Сортирай по дни' );
				$oResponse->setField( 'quittances',			'обезщетение', 				'Сортирай по дни' );
				$oResponse->setField( 'hospital',			'болнични', 				'Сортирай по болнични' );
				$oResponse->setField( 'unpaid',				'неплатени', 				'Сортирай по неплатени' );
				$oResponse->setField( 'unconfirm',			'непотвърдени', 			'Сортирай по непотвърдени' );
				$oResponse->setField( 'updated_user', 		'...', 						'Сортиране по последно редактирал', 'images/dots.gif' );
				$oResponse->setField( '', 					'',  						'Изтрий', "images/cancel.gif", "delLeave", '');
				
				$oResponse->setFIeldLink( 'year',		'openLeave' );
				$oResponse->setFIeldLink( 'due_days',	'openLeave' );
			}
			
			foreach( $oResponse->oResult->aData as $nKey => $aValue )
			{
				$sTitle = isset( $aValue['unconfirm_leaves'] ) ? $aValue['unconfirm_leaves'] : "";
				$oResponse->setDataAttributes( $nKey, "unconfirm", array( "title" => $sTitle ) );
			}
			
			$aPersonData = $oPerson->getRecord( $aParams['id'] );
			
			if( !empty( $aPersonData ) )
			{
				if( !empty( $aPersonData['is_substitute_needed'] ) )
				{
					$oResponse->setFormElement( "form1", "nIsSubstituteNeeded", array( "checked" => "checked" ) );
				}
				else
				{
					$oResponse->setFormElement( "form1", "nIsSubstituteNeeded", array( "checked" => "" ) );
				}
			}
			
			print( $oResponse->toXML() );
			break;
	}
	
?>