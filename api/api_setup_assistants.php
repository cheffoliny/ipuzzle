<?php

	class ApiSetupAssistants
	{
		public function load( DBResponse $oResponse )
		{
			global $db_sod;
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
//			$aRegions = array();
//			$oRegions = new DBBase( $db_sod, 'offices' );
//			
//			$nFirmMatch = empty($aParams['nIDFirm']) ? 0 : (int) $aParams['nIDFirm'];
//			
//			if( $nFirmMatch )$oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", " id_firm=$nFirmMatch " ), "name" );
//			else $oRegions->getResult( $aRegions, NULL, array(" to_arc=0 "), "name" );
//			
//			$oResponse->setFormElement( 'form1', 'nIDRegion' );
//			
//			$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => 0 ), 'Всички Региони' );
//			foreach( $aRegions as $aRegion )
//			{
//				$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
//			}
//			if( isset( $aParams['nIDRegion'] ) )
//			{
//				$oResponse->setFormElementAttribute( 'form1', 'nIDRegion', 'value', $aParams['nIDRegion'] );
//			}
			//End Set Regions
			
			if( !isset( $aParams['nIDFirm'] ) && !isset( $aParams['nIDRegion'] ) )
			{
				$oOffices = new DBOffices();
				
				$oOffices->retrieveLoggedUserOffice( 'nIDFirm', 'nIDRegion', $oResponse );
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oAssistants = new DBAssistants();
			$oAssistants->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Рекламни сътрудници", "assistants" );
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
			
			$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => 0 ), 'Всички Региони' );
			foreach( $aRegions as $aRegion )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
			}
			//End Set Regions
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oAssistants = new DBAssistants();
			$oAssistantsCities = new DBAssistantsCities();
			$oAssistants->delete( $nID );
			$oAssistantsCities->select( "UPDATE assistants_cities SET to_arc = 1 WHERE id_assistant = {$nID}" );
			
			$oResponse->printResponse();
		}
	}

?>