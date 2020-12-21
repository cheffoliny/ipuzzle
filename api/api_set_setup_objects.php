<?php
	
	$oObjects = 	new DBBase( $db_sod, 'region_objects' );
	$oFirms = 		new DBBase( $db_sod, 'firms' );
	$oRegions = 	new DBBase( $db_sod, 'offices' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SaveObject( $aParams );
		break;
		
		default : 
			loadObject( $aParams );
		break;
	}
	
	function SaveObject( $aParams )
	{
		global $oObjects, $oResponse;

		$nNum = !empty( $aParams['num'] ) && is_numeric($aParams['num']) ? $aParams['num'] : 0;

		if( empty( $nNum ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето Номер на Обект!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето Име на Обект!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		if( empty( $aParams['id_reg'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не са въведени региони!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//Проверка за повторение в номера
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " num = $nNum ";
		
		if( $nResult = $oObjects->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		if( !empty( $aDuplicate ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този номер!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		$aObject = array();
		$aObject['id'] 			= $aParams['id'];
		$aObject['id_office'] 	= $aParams['id_reg'];
		$aObject['num'] 		= $aParams['num'];
		$aObject['name'] 		= $aParams['name'];
		
		if( $nResult = $oObjects->update( $aObject ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			print( $oResponse->toXML() );
			die();
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadObject( $aParams )
	{
		global $oObjects, $oRegions, $oFirms, $oResponse, $db;

		$aFirms = $aRegions = array();

		$id = (int) $aParams['id'];

		if( $id == 0 )
		{
			if( $aParams['status'] == 'updated' )
			{
				$oFirms->getResult( $aFirms, NULL, array(" to_arc=0 "), "name" );
				$oResponse->setFormElement( 'form1', 'id_firm' );
				
				$sel = false;
				$def = 0;
				foreach( $aFirms as $aFirm )
				{
					if(!$sel)
					{
						$def = $aFirm['id'];
						$sel = true;
					}
					$arr = array();
					if( !empty($aParams['id_firm']) )
						if( $aFirm['id']==$aParams['id_firm'] )
						{
							$arr = array('selected'=>'selected');
						}
					
					$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge(array('value'=>$aFirm['id']), $arr), sprintf("%s [%s]",$aFirm['name'], $aFirm['code']) );
				}
		
				$firm_match = empty($aParams['id_firm']) ? $def : (int) $aParams['id_firm'];
				
				$oRegions->getResult( $aRegions, NULL, array(" to_arc=0 ", " id_firm=$firm_match "), "name" );
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				if( empty($aRegions) ) 
					$oResponse->setFormElementChild( 'form1', 'id_reg', array(), '');
				else
					foreach( $aRegions as $aRegion )
					{
						$oResponse->setFormElementChild( 'form1', 'id_reg', array('value'=>$aRegion['id']), sprintf("%s [%s]", $aRegion['name'], $aRegion['code']) );
					}
			}
			else
			{
				$oFirms->getResult( $aFirms, NULL, array(" to_arc=0 "), "name" );
				$oResponse->setFormElement( 'form1', 'id_firm' );

				foreach( $aFirms as $aFirm )
				{
					$arr = array();
					if( $aFirm['id']==$aParams['id_f'] )
					{
						$arr = array('selected'=>'selected');
					}
					
					$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge(array('value'=>$aFirm['id']), $arr), sprintf("%s [%s]",$aFirm['name'], $aFirm['code']) );
				}
		
				$oRegions->getResult( $aRegions, NULL, array(" to_arc=0 ", " id_firm={$aParams['id_f']} "), "name" );
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				if( empty($aRegions) ) 
					$oResponse->setFormElementChild( 'form1', 'id_reg', array(), '');
				else
					foreach( $aRegions as $aRegion )
					{
						if( $aParams['id_r'] != 0 )
						{
							$arr = array();
							if( $aRegion['id']==$aParams['id_r'] )
							{
								$arr = array('selected'=>'selected');
							}
						}
						
						$oResponse->setFormElementChild( 'form1', 'id_reg', array_merge(array('value'=>$aRegion['id']), $arr), sprintf("%s [%s]", $aRegion['name'], $aRegion['code']) );
					}
				$oResponse->setFormElementAttribute( 'form1', 'status', 'value', 'updated' );
			}
		}
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oObjects->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}

			if( $aParams['status'] != 'updated' )
			{
				$neededFirm = array();
				$oRegions->getRecord( $aData['id_office'], $neededFirm );
				
				$oFirms->getResult( $aFirms, NULL, array(" to_arc=0 "), "name" );
				$oResponse->setFormElement( 'form1', 'id_firm' );
				
				foreach( $aFirms as $aFirm )
				{
					$arr = array();
					if( $aFirm['id']==$neededFirm['id_firm'] )
					{
						$arr = array('selected'=>'selected');
					}
					
					$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge(array('value'=>$aFirm['id']), $arr), sprintf("%s [%s]",$aFirm['name'], $aFirm['code']) );
				}
		
				$firm_match = $neededFirm['id_firm'];
				
				$oRegions->getResult( $aRegions, NULL, array(" to_arc=0 ", " id_firm=$firm_match "), "name" );
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				if( empty($aRegions) ) 
					$oResponse->setFormElementChild( 'form1', 'id_reg', array(), '');
				else
					foreach( $aRegions as $aRegion )
					{
						$arr = array();
						if( $aRegion['id']==$aData['id_office'] )
						{
							$arr = array('selected'=>'selected');
						}
						
						$oResponse->setFormElementChild( 'form1', 'id_reg', array_merge(array('value'=>$aRegion['id']), $arr), sprintf("%s [%s]", $aRegion['name'], $aRegion['code']) );
					}
				$oResponse->setFormElementAttribute( 'form1', 'status', 'value', 'updated' );
			}
			else 
			{
				$firm_match = $aParams['id_firm'];
				
				$oRegions->getResult( $aRegions, NULL, array(" to_arc=0 ", " id_firm=$firm_match "), "name" );
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				if( empty($aRegions) ) 
					$oResponse->setFormElementChild( 'form1', 'id_reg', array(), '');
				else
					foreach( $aRegions as $aRegion )
					{
						$arr = array();
						if( $aRegion['id']==$aData['id_office'] )
						{
							$arr = array('selected'=>'selected');
						}
						
						$oResponse->setFormElementChild( 'form1', 'id_reg', array_merge(array('value'=>$aRegion['id']), $arr), sprintf("%s [%s]", $aRegion['name'], $aRegion['code']) );
					}
				$oResponse->setFormElementAttribute( 'form1', 'status', 'value', 'updated' );
			}
			
			$oResponse->setFormElement('form1', 'num', array(), $aData['num']);
			$oResponse->setFormElement('form1', 'name', array(), $aData['name']);
			
			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>