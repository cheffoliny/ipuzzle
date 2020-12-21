<?php

	$oDocType = new DBBase( $db_personnel, 'document_types' );

	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('edit_document_types', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}

	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				$oDBPersonDocs	= new DBPersonDocs();
				$nCount			= $oDBPersonDocs->getCountDocsByID($nID);
				if (empty($nCount)) {
					if( $nReseul = $oDocType->toARC( $nID ) != DBAPI_ERR_SUCCESS )
						$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
				} else if ($nCount==1) $oResponse->setError(DBAPI_ERR_ALERT, "Не може да премахнете този тип съпровождащи документи! \nИма {$nCount} такъв документ!");
			 	else $oResponse->setError(DBAPI_ERR_ALERT, "Не може да премахнете този тип съпровождащи документи! \nИма общо {$nCount} такива документа!");
				
				$aParams['api_action'] = 'result';
			break;
		
		default: 
			break;
	}
	
	class MyHandler extends APIHandler
	{
		function setFields( $aParams )
		{
			global $oResponse;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('document_types_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$oResponse->setField( 'name', 	'наименование', 'Сортирай по наименование' );
			if($right_edit)
			{
				$oResponse->setField( '', 		'', 			'', 'images/cancel.gif', 'deleteDocumentType', '');
				$oResponse->setFieldLink( 'name', 'viewType' );
			}
		}
		
		function getReport( $aParams )
		{
			$aWhere = array();
			$aWhere[] = " t.to_arc = 0 ";
			
			$sQuery = sprintf(" 
				SELECT 
					SQL_CALC_FOUND_ROWS 
					t.id as _id, 
					t.id, 
					t.name
				FROM 
					%s t 
				", 
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
	}
	
	$oHandler = new MyHandler( $oDocType, 'name', 'document_types', 'Съпровождащи Документи' );
	$oHandler->Handler( $aParams );

?>