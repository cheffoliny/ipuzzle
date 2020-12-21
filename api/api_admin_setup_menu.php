<?php		
	$oMenu = new DBMenu();
	
	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}

	switch ($aParams['api_action']) {
		case "delete" : 
			if( !empty($aParams['id']) ) {
				$oResponse->setError( $oMenu->delete($aParams['id']) );
				if ($oResponse->oError->nCode != 0 ) {
					print( $oResponse->toXML() );
					break;
				}
			}
		
		default :
			if( empty( $aParams['sfield'] ) )
				$aParams['sfield'] = "menu_order";
				
			if( empty( $aParams['stype'] ) )
				$aParams['stype'] = DBAPI_SORT_ASC;
				
			if( empty( $aParams['current_page'] ) )
				$aParams['current_page'] = 1;	

			if ( $aParams['api_action']=="export_to_xls"  || $aParams['api_action']=="export_to_pdf" ) {
				$aParams['current_page'] = 0;
			}
			
			if( ($nResult = $oMenu->getResult( 
				$aParams['sfield'], 
				$aParams['stype'], 
				$aParams['current_page'],
				$oResponse)) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult );
			} else {
				$oResponse->setField(	'menu_order',	'Ред',			'Сортирай по ред' );
				$oResponse->setField(	'title',		'Наименование', 'Сортирай по наименование' );
				$oResponse->setField(	'filename', 	'Файл',			'Сортирай по файл' );
				
				if ($right_edit) {
					$oResponse->setField(	'btn_delete',  	'', 			'Изтрий', "images/cancel.gif", "deleteMenu", '');
					$oResponse->setFIeldLink( 'title',		'setupMenu' );
					$oResponse->setFIeldLink( 'filename',	'setupMenu' );
				}
			}

			foreach ($oResponse->oResult->aData as $key => $value){
				if( isset($value['level']) && ($value['level'] > 0) ) {
					$oResponse->setDataAttributes( $key, 'title', array( "style"=>"padding-left: ".strval($value["level"] * 5)."mm" ) );
				} else {
					$oResponse->setDataAttributes( $key, 'title', array( "style"=>"font-weight:bold" ) );
				}
			}

			if ( $aParams['api_action'] == "export_to_xls" ) {
				$oResponse->toXLS("menu_".date('y_m_d').".xls", "Меню за системата");
			} elseif ( $aParams['api_action'] == "export_to_pdf" ) {
				$oResponse->toPDF("Меню за системата",'P');
			} else {
				print( $oResponse->toXML() );
			}

			break;
	}
?>