<?php
	
	$oRegions = new DBBase( $db_sod, 'offices' );
	$oFirms = 	new DBBase( $db_sod, 'firms' );
	
	switch( $aParams['api_action'] )
	{
		case "save":
			SaveRegion( $aParams );
		break;
		
		case "updateareas":
			$nIDCity = !empty( $aParams['nAddressCity'] ) ? $aParams['nAddressCity'] : 0;
			//Set Areas
			$oAreas = new DBCityAreas();
			$aAreas = $oAreas->getAreasByCity( $nIDCity );
			
			$oResponse->setFormElement( 'form1', 'nAddressArea' );
			$oResponse->setFormElementChild( 'form1', 'nAddressArea', array( 'value' => 0 ), "-- Няма Данни --" );
			foreach( $aAreas as $aArea )
			{
				$oResponse->setFormElementChild( 'form1', 'nAddressArea', array( 'value' => $aArea['id'] ), $aArea['name'] );
			}
			//End Set Areas
			//Set Streets
			$oStreets = new DBCityStreets();
			$aStreets = $oStreets->getStreetsByCity( $nIDCity );
			
			$oResponse->setFormElement( 'form1', 'nAddressStreet' );
			$oResponse->setFormElementChild( 'form1', 'nAddressStreet', array( 'value' => 0 ), "-- Няма Данни --" );
			foreach( $aStreets as $aStreet )
			{
				$oResponse->setFormElementChild( 'form1', 'nAddressStreet', array( 'value' => $aStreet['id'] ), $aStreet['name'] );
			}
			//End Set Streets
		break;
		
		default:
			loadRegion( $aParams['id'] );
		break;
	}
	
	function SaveRegion( $aParams )
	{
		global $oRegions, $oResponse;
		
		$oDBOfficesDirections = new DBOfficesDirections();
		
		$nCode = !empty( $aParams['code'] ) && is_numeric( $aParams['code'] ) ? $aParams['code'] : 0;
		
		if( empty( $nCode ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето Код на Региона!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['id_firm'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не са въведени фирми!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//Проверка за повторение в кода
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = $nCode ";
		$aWhere[] = " to_arc = 0";
		
		if( $nResult = $oRegions->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		if( !empty( $aDuplicate ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето Име на Регион!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//Save Directions
		$aDirections = $aParams['directions_current'];
		
		$nResult = $oDBOfficesDirections->deleteByOffice( $aParams['id'] );
		if( $nResult != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Грешка при изпълнение на операцията!" );
			return $nResult;
		}
		
		$oDBOfficesDirections->StartTrans();
		foreach( $aDirections as $nKey => $nIDDirection )
		{
			$aDirData = array();
			$aDirData['id_office'] = $aParams['id'];
			$aDirData['id_direction'] = ( int ) $nIDDirection;
			
			$oDBOfficesDirections->update( $aDirData );
		}
		$oDBOfficesDirections->CompleteTrans();
		//End Save Directions
		
		//Проверка за валидност на коефициентите
		$aParams['factor_tech_support'] = (float) $aParams['factor_tech_support'];
		$aParams['factor_tech_distance'] = (float) $aParams['factor_tech_distance'];
		$aParams['factor_km_over'] = (float) $aParams['factor_km_over'];
		$aParams['factor_km_below'] = (float) $aParams['factor_km_below'];
		$aParams['factor_object_single_from_arrange'] = (float) $aParams['factor_object_single_from_arrange'];
		$aParams['km_per_roadlist'] = (int) $aParams['km_per_roadlist'];
		$aParams['max_visits'] = (int) $aParams['max_visits'];
		
		if( $aParams['factor_tech_support'] > 9.99 					|| $aParams['factor_tech_support'] < 0 					||
			$aParams['factor_tech_distance'] > 9.99 				|| $aParams['factor_tech_distance'] < 0 				||
			$aParams['factor_km_over'] > 9.99						|| $aParams['factor_km_over'] < 0						||
			$aParams['factor_km_below'] > 9.99						|| $aParams['factor_km_below'] < 0						||
			$aParams['factor_object_single_from_arrange'] > 9.99	|| $aParams['factor_object_single_from_arrange'] < 0 )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведена е некоректна стойност!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		$aRegion = array();
		$aRegion['id'] 									= $aParams['id'];
		$aRegion['code'] 								= $aParams['code'];
		$aRegion['id_firm'] 							= $aParams['id_firm'];
		$aRegion['name'] 								= $aParams['name'];
		$aRegion['factor_tech_support'] 				= $aParams['factor_tech_support'];
		$aRegion['factor_tech_distance'] 				= $aParams['factor_tech_distance'];
		$aRegion['factor_km_over'] 						= $aParams['factor_km_over'];
		$aRegion['factor_km_below'] 					= $aParams['factor_km_below'];
		$aRegion['factor_object_single_from_arrange'] 	= $aParams['factor_object_single_from_arrange'];
		$aRegion['km_per_roadlist'] 					= $aParams['km_per_roadlist'];
		$aRegion['max_visits'] 							= $aParams['max_visits'];
		$aRegion['is_reaction'] 						= $aParams['nIsReaction'];
		$aRegion['is_admin'] 							= $aParams['nIsAdmin'];
		$aRegion['is_tech'] 							= $aParams['nIsTech'];
		$aRegion['address_city'] 						= $aParams['nAddressCity'];
		$aRegion['address_area'] 						= $aParams['nAddressArea'];
		$aRegion['address_street'] 						= $aParams['nAddressStreet'];
		$aRegion['address_num'] 						= $aParams['nNumber'];
		
		if( $nResult = $oRegions->update( $aRegion ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadRegion( $nID )
	{
		global $oRegions, $oFirms, $oResponse, $db, $aParams;
		
		$aFirms = array();
		
		$id = (int) $nID;
		
		$oCities 				= new DBCities();
		$oAreas 				= new DBCityAreas();
		$oStreets 				= new DBCityStreets();
		$oDBDirections 			= new DBDirections();
		$oDBOfficesDirections 	= new DBOfficesDirections();
		
		//Set Cities
		$aCities = $oCities->getCities();
		//APILog::Log(0, $aCities);
		$oResponse->setFormElement( 'form1', 'nAddressCity' );
		$oResponse->setFormElementChild( 'form1', 'nAddressCity', array( 'value' => 0 ), "-- Няма Данни --" );
		
		foreach( $aCities as $nCode => $sCity ) {
			$oResponse->setFormElementChild( 'form1', 'nAddressCity', array( 'value' => $nCode ), $sCity );
		}
		//End Set Cities
		
		//Load Directions
		$aDirections = $oDBDirections->getDirections();
		
		$oResponse->setFormElement( "form1", "directions_all" );
		$oResponse->setFormElement( "form1", "directions_current" );
		//End Load Directions
		
		if( $id == 0 )
		{
			$oFirms->getResult( $aFirms, NULL, array( " to_arc=0 " ), "name" );
			
			$oResponse->setFormElement( 'form1', 'id_firm' );
			
			foreach( $aFirms as $aFirm )
			{
				$arr = array();
				if( $aFirm['id'] == $aParams['id_f'] )
					$arr = array( 'selected' => 'selected' );
				
				$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge( array( 'value' => $aFirm['id'] ), $arr ), sprintf( "%s [%s]", $aFirm['name'], $aFirm['code'] ) );
			}
			
			//Set Areas
			$oResponse->setFormElement( 'form1', 'nAddressArea' );
			$oResponse->setFormElementChild( 'form1', 'nAddressArea', array( 'value' => 0 ), "-- Няма Данни --" );
			//End Set Areas
			
			//Set Streets
			$oResponse->setFormElement( 'form1', 'nAddressStreet' );
			$oResponse->setFormElementChild( 'form1', 'nAddressStreet', array( 'value' => 0 ), "-- Няма Данни --" );
			//End Set Streets
			
			//Set Directions
			foreach( $aDirections as $aDirection )
			{
				$oResponse->setFormElementChild( "form1", "directions_all", array( "value" => $aDirection['id'] ), $aDirection['name'] );
			}
			//End Set Directions
			
			$oResponse->setFormElement( 'form1', 'factor_tech_support', 				array(), '1.00' );
			$oResponse->setFormElement( 'form1', 'factor_tech_distance', 				array(), '1.00' );
			$oResponse->setFormElement( 'form1', 'factor_km_over', 						array(), '1.00' );
			$oResponse->setFormElement( 'form1', 'factor_km_below', 					array(), '1.00' );
			$oResponse->setFormElement( 'form1', 'factor_object_single_from_arrange', 	array(), '1.00' );
		}
		
		if( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oRegions->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement( 'form1', 'code', array(), $aData['code'] );
			
			$oFirms->getResult( $aFirms, NULL, array( " to_arc=0 " ), "name" );
			
			$oResponse->setFormElement( 'form1', 'id_firm' );
			
			foreach( $aFirms as $aFirm )
			{
				$arr = array();
				if( $aFirm['id'] == $aData['id_firm'] )
					$arr = array( 'selected' => 'selected' );
				
				$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge( array( 'value' => $aFirm['id'] ), $arr ), sprintf( "%s [%s]", $aFirm['name'], $aFirm['code'] ) );
			}
			
			$nIDCity = !empty( $aData['address_city'] ) ? $aData['address_city'] : 0;
			//Set Areas
			$aAreas = $oAreas->getAreasByCity( $nIDCity );
			
			$oResponse->setFormElement( 'form1', 'nAddressArea' );
			$oResponse->setFormElementChild( 'form1', 'nAddressArea', array( 'value' => 0 ), "-- Няма Данни --" );
			foreach( $aAreas as $aArea )
			{
				$oResponse->setFormElementChild( 'form1', 'nAddressArea', array( 'value' => $aArea['id'] ), $aArea['name'] );
			}
			//End Set Areas
			
			//Set Streets
			$aStreets = $oStreets->getStreetsByCity( $nIDCity );
			
			$oResponse->setFormElement( 'form1', 'nAddressStreet' );
			$oResponse->setFormElementChild( 'form1', 'nAddressStreet', array( 'value' => 0 ), "-- Няма Данни --" );
			foreach( $aStreets as $aStreet )
			{
				$oResponse->setFormElementChild( 'form1', 'nAddressStreet', array( 'value' => $aStreet['id'] ), $aStreet['name'] );
			}
			//End Set Streets
			
			//Set Directions
			$aOfficeDirections = $oDBOfficesDirections->getOfficeDirectionsIDs( $id );
			
			foreach( $aDirections as $aDirection )
			{
				if( in_array( $aDirection['id'], $aOfficeDirections ) )
				{
					$oResponse->setFormElementChild( "form1", "directions_current", array( "value" => $aDirection['id'] ), $aDirection['name'] );
				}
				else
				{
					$oResponse->setFormElementChild( "form1", "directions_all", array( "value" => $aDirection['id'] ), $aDirection['name'] );
				}
			}
			//End Set Directions
			
			$oResponse->setFormElement( 'form1', 'name', 								array(), $aData['name'] );
			$oResponse->setFormElement( 'form1', 'factor_tech_support', 				array(), $aData['factor_tech_support'] );
			$oResponse->setFormElement( 'form1', 'factor_tech_distance', 				array(), $aData['factor_tech_distance'] );
			$oResponse->setFormElement( 'form1', 'factor_km_over', 						array(), $aData['factor_km_over'] );
			$oResponse->setFormElement( 'form1', 'factor_km_below', 					array(), $aData['factor_km_below'] );
			$oResponse->setFormElement( 'form1', 'factor_object_single_from_arrange', 	array(), $aData['factor_object_single_from_arrange'] );
			$oResponse->setFormElement( 'form1', 'km_per_roadlist', 					array(), $aData['km_per_roadlist'] );
			$oResponse->setFormElement( 'form1', 'max_visits', 							array(), $aData['max_visits'] );
			
			if( $aData['is_reaction'] )
				$oResponse->setFormElement( 'form1', 'nIsReaction', array( "checked" => "checked" ) );
			else
				$oResponse->setFormElement( 'form1', 'nIsReaction', array( "checked" => "" ) );
			
			if( $aData['is_admin'] )
				$oResponse->setFormElement( 'form1', 'nIsAdmin', array( "checked" => "checked" ) );
			else
				$oResponse->setFormElement( 'form1', 'nIsAdmin', array( "checked" => "" ) );
			
			if( $aData['is_tech'] )
				$oResponse->setFormElement( 'form1', 'nIsTech', array( "checked" => "checked" ) );
			else
				$oResponse->setFormElement( 'form1', 'nIsTech', array( "checked" => "" ) );
			
			$oResponse->setFormElementAttribute( 'form1', 'nAddressCity', 	'value', $aData['address_city'] );
			$oResponse->setFormElementAttribute( 'form1', 'nAddressArea', 	'value', $aData['address_area'] );
			$oResponse->setFormElementAttribute( 'form1', 'nAddressStreet', 'value', $aData['address_street'] );
			$oResponse->setFormElement( 'form1', 'nNumber', array(), $aData['address_num'] );
			//debug($aData);
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>