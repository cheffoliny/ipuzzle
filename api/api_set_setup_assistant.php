<?php

	class ApiSetSetupAssistant
	{
		public function get( DBResponse $oResponse )
		{
			global $db_sod;
			$nID = Params::get("nID", 0);
			$aParams = Params::getAll();
			
			//Set Firms
			$oFirms = new DBFirms();
			$aFirms = $oFirms->getFirms2();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array() );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( 'value' => 0 ), 'Всички Фирми' );
			
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( 'value' => $aFirm['id'] ), $aFirm['name'] );
			}
			if( isset( $aParams['nIDFirm'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDFirm', 'value', $aParams['nIDFirm'] );
			}
			//End Set Firms
			
			//Set Regions
			$aRegions = array();
			$oRegions = new DBBase( $db_sod, 'offices' );
			
			$nFirmMatch = empty($aParams['nIDFirm']) ? 0 : (int) $aParams['nIDFirm'];
			
			//Accessable Regions
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sCondition = " id IN ({$sAccessable}) ";
			}
			else $sCondition = " 1 ";
			//End Accessable Regions
			
			if( $nFirmMatch )$oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", " id_firm=$nFirmMatch ", $sCondition ), "name" );
			else $oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", $sCondition ), "name" );
			
			$oResponse->setFormElement( 'form1', 'nIDRegion' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => 0 ), 'Всички Региони');
			foreach( $aRegions as $aRegion )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
			}
			if( isset( $aParams['nIDRegion'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDRegion', 'value', $aParams['nIDRegion'] );
			}
			//End Set Regions
			
			//Set Persons
			$aPersons = array();
			$oPersons = new DBPersonnel();
			
			$nOfficeMatch = empty( $aParams['nIDRegion'] ) ? 0 : (int) $aParams['nIDRegion'];
			$aPersons = $oPersons->getPersonnelsByIDOffice2( $nOfficeMatch );
			
			$oResponse->setFormElement( 'form1', 'nIDPerson' );
			
			foreach( $aPersons as $aPerson )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( 'value' => $aPerson['id'] ), sprintf( "%s", $aPerson['name'] ) );
			}
			if( isset( $aParams['nIDPerson'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDPerson', 'value', $aParams['nIDPerson'] );
			}
			//End Set Persons
			
			//Set Cities
			$oCities = new DBCities();
			$aCities = $oCities->getCities2();
			
			$oResponse->setFormElement( 'form1', 'Offices', array(), '' );
			foreach( $aCities as $aCity )
			{
				$oResponse->setFormElementChild( 'form1', 'Offices', array( "value" => $aCity['id'] ), sprintf( '%s [%s]', $aCity['name'], $aCity['post_code'] ) );
			}
			//End Set Cities
			
			if( !empty( $nID ) )
			{
				$oAssistants = new DBAssistants();
				$aAssistant = $oAssistants->getRecord( $nID );
				
				$oResponse->setFormElementAttribute( 'form1', 'nIDPerson', 	'value', $aAssistant['id_person'] );
				$oResponse->setFormElement( 'form1', 'nNextNum', array( 'value' => $aAssistant['next_num'] ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function genregions( DBResponse $oResponse )
		{
			global $db_sod;
			$aParams = Params::getAll();
			
			//Set Regions
			$aRegions = array();
			$oRegions = new DBBase( $db_sod, 'offices' );
			
			$nFirmMatch = empty($aParams['nIDFirm']) ? 0 : (int) $aParams['nIDFirm'];
			
			//Accessable Regions
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sCondition = " id IN ({$sAccessable}) ";
			}
			else $sCondition = " 1 ";
			//End Accessable Regions
			
			if( $nFirmMatch )$oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", " id_firm=$nFirmMatch ", $sCondition ), "name" );
			else $oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", $sCondition ), "name" );
			
			$oResponse->setFormElement( 'form1', 'nIDRegion' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => 0 ), 'Всички Региони');
			foreach( $aRegions as $aRegion )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
			}
			//End Set Regions
			
			$oResponse->printResponse();
		}
		
		public function genpersons( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			//Set Persons
			$aPersons = array();
			$oPersons = new DBPersonnel();
			
			$nOfficeMatch = empty( $aParams['nIDRegion'] ) ? 0 : (int) $aParams['nIDRegion'];
			$aPersons = $oPersons->getPersonnelsByIDOffice2( $nOfficeMatch );
			
			$oResponse->setFormElement( 'form1', 'nIDPerson' );
			
			foreach( $aPersons as $aPerson )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( 'value' => $aPerson['id'] ), sprintf( "%s", $aPerson['name'] ) );
			}
			//End Set Persons
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			$nIDPerson = Params::get( "nIDPerson", 0 );
			$nNextNum = Params::get( "nNextNum", 0 );
			
			$aSelectedOffices = Params::get( "selected_offices" );
			
			if( empty( $nIDPerson ) || empty( $nNextNum ) )
				throw new Exception("Въведена е невалидна стойност!", DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $aSelectedOffices ) )
				throw new Exception("Въведете населени места!", DBAPI_ERR_INVALID_PARAM);
			
			$aDataAssistants = array();
			$aDataAssistants['id'] = Params::get('nID', 0);
			$aDataAssistants['id_person'] = $nIDPerson;
			$aDataAssistants['next_num'] = $nNextNum;
			
			$oAssistants = new DBAssistants();
			$oAssistantsCities = new DBAssistantsCities();
			$oAssistants->update( $aDataAssistants );
			
			//Prepare Assistant Cities Table
			$oAssistantsCities->select( "DELETE FROM assistants_cities WHERE id_assistant={$nID}" );
			//End Prepare Assistant Cities Table
			
			$aDataAssistantCities['id_assistant'] = $aDataAssistants['id'];
			foreach( $aSelectedOffices as $key => $value )
			{
				$aDataAssistantCities['id'] = 0;
				$aDataAssistantCities['id_city'] = $value;
				$oAssistantsCities->update( $aDataAssistantCities );
			}
		}
	}
	
?>