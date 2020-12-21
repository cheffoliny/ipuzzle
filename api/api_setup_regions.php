<?php

	$oRegions = 	new DBBase( $db_sod, 'offices' );
	$oFirms = 		new DBBase( $db_sod, 'firms' );

	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('regions_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}

	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				
				$oDBObjects				= new DBObjects();
				$oDBOfficesDirections 	= new DBOfficesDirections();
				
				$nCount	= $oDBObjects->getCountOfficesByID( $nID );
				if( empty( $nCount ) )
				{
					if( $nReseul = $oRegions->toARC( $nID ) != DBAPI_ERR_SUCCESS )
						$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
				}
				else if( $nCount == 1 )$oResponse->setError( DBAPI_ERR_ALERT, "Не може да премахнете този регион! \nКъм него има привързан {$nCount} обект!" );
			 	else $oResponse->setError( DBAPI_ERR_ALERT, "Не може да премахнете този регион! \nКъм него има привързани {$nCount} обекта!" );
				
			 	$oDBOfficesDirections->deleteByOffice( $nID );
			 	
				$aParams['api_action'] = 'result';
			break;
		
		case 'generate' : 
				$aFirms = array();
				
				$oFirms->getResult( $aFirms, NULL, array(" to_arc=0 "), "name" );
				$oResponse->setFormElement( 'form1', 'id_firm' );
				
				foreach( $aFirms as $aFirm )
				{
					$arr = array();
					if( !empty($aParams['id_firm']) )
						if( $aFirm['id']==$aParams['id_firm'] )
						{
							$arr = array('selected'=>'selected');
						}
					
					$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge(array('value'=>$aFirm['id']), $arr), sprintf("%s [%s]",$aFirm['name'], $aFirm['code']) );
				}
				
				print( $oResponse->toXML() );
			break;
		
		default: 
			break;
	}
	
	class MyHandler extends APIHandler
	{
		function setFields( $aParams )
		{
			global $oResponse, $right_edit;
			
			$oResponse->setField( 'code', 		'код', 			'Сортирай по код', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_NUMBER ) );
			$oResponse->setField( 'name', 		'име', 			'Сортирай по име на регион', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'name_firm', 	'име на фирма', 'Сортирай по име на фирма', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );

			$oResponse->setField( 'factor_tech_support', 				'коеф. наработка за монтаж и профилактика', 	'Сортирай по коеф. наработка', 							NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'factor_tech_distance', 				'коеф. отдалеченост за техник', 				'Сортирай по коеф. за отдалеченост', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'factor_km_over', 					'коеф. преразход за км', 						'Сортирай по коеф. цена преразход', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'factor_km_below', 					'коеф. икономия за км', 						'Сортирай по коеф. цена икономия', 						NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'factor_object_single_from_arrange', 	'коеф. аранж. еднократно задължение', 			'Сортирай по коеф. аранжиране еднократно задължение', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'km_per_roadlist', 					'признати км пътен лист', 						'Сортирай по признати км', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'max_visits', 						'признати бр. обходи за пътен лист', 			'Сортирай по признати бр. обходи за пътен лист', 		NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			
			if($right_edit)
			{
				$oResponse->setField( '',			'',					'', 'images/cancel.gif', 'deleteRegion', '');
				$oResponse->setFieldLink( 'name', 'viewRegion' );
			}
		}
		
		function getReport( $aParams )
		{
			$aWhere = array();
			
			$searchID = empty( $aParams['id_firm'] ) ? 1 : (int) $aParams['id_firm'];
			$aWhere[] = sprintf(" f.id = '%s' ", $searchID );
			$aWhere[] = " t.to_arc = 0 ";
			
			$sQuery = sprintf(" 
				SELECT SQL_CALC_FOUND_ROWS
					t.id as _id,
					t.id,
					t.code,
					CONCAT( f.name, ' [', f.code, ']' ) as name_firm,
					t.name,
					t.factor_tech_support,
					t.factor_tech_distance,
					t.factor_km_over,
					t.factor_km_below,
					t.factor_object_single_from_arrange,
					t.km_per_roadlist,
					t.max_visits
				FROM
					%s t
					LEFT JOIN firms f ON f.id = t.id_firm
				",
				$this->_oBase->_sTableName
			);
			
			return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
		}
	}
	
	if( $aParams['api_action'] != 'generate' )
	{
		$oHandler = new MyHandler( $oRegions, 'name', 'regions', 'Региони' );
		$oHandler->Handler( $aParams );
	}

?>