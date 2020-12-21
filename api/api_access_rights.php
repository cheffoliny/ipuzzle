<?php
	class ApiAccessRights
	{
		public function setFields( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oAccessLevels = new DBAccessLevel();
			$oFirms = new DBFirms();
			$oPositions = new DBPositions();
			
			//Load Firms and Offices
			$aFirms = $oFirms->getFirms4();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => '0' ) ), "--Изберете--" );
			foreach( $aFirms as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => $key ) ), $value );
			}
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
			
			if( isset( $aParams['nIDFirm'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDFirm', 'value', $aParams['nIDFirm'] );
			}
			if( isset( $aParams['nIDOffice'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDOffice', 'value', $aParams['nIDOffice'] );
			}
			//End Load Firms and Offices
			
			//Get Rights List
			$aAccessLevels = $oAccessLevels->getAccessLevels();
			
			$oResponse->setFormElement( 'form1', 'all_rights', array(), "" );
			$oResponse->setFormElement( 'form1', 'search_rights', array(), "" );
			foreach( $aAccessLevels as $aAccessLevel )
			{
				$oResponse->setFormElementChild( 'form1', 'all_rights', array( "value" => $aAccessLevel['name'] ), $aAccessLevel['description'] );
			}
			//End Get Rights List
			
			//Get Possitions
			$aPositions = $oPositions->getPositions();
			
			$oResponse->setFormElement( 'form1', 'nIDPosition', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDPosition', array( "value" => 0 ), "--Изберете--" );
			foreach( $aPositions as $nKey => $sValue )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDPosition', array( "value" => $nKey ), $sValue );
			}
			
			if( isset( $aParams['nIDPosition'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDPosition', 'value', $aParams['nIDPosition'] );
			}
			//End Get Possitions
			
			$oResponse->printResponse();
		}
		
		public function getOffices( DBResponse $oResponse )
		{
			$nFirm = Params::get( 'nIDFirm' );
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			
			if( !empty( $nFirm ) )
			{
				$oOffices = new DBOffices();
				$aOffices = $oOffices->getOfficesByIDFirm( $nFirm );
				
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "--Всички--" );
				foreach( $aOffices as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => $key ) ), $value);
				}
			}
			else
			{
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oAccessLevel = new DBAccessLevel();
			
			$oResponse->setFormElement( "form1", "sMadeChanges", array( "value" => "" ) );
			
			$oAccessLevel->getAccessRightsReport( $aParams, $oResponse );
			$oResponse->setFormElementAttribute( 'form1', 'search_rights', 'value', 0 );
			
			$oResponse->printResponse( 'Права на достъп', 'access_rights' );
		}
		
		public function changeLevels( DBResponse $oResponse )
		{
			$oAccessAccounts = 		new DBAccessAccount();
			$oAccessLevel = 		new DBAccessLevel();
			$oAccessLevelProfile = 	new DBAccessLevelProfile();
			
			$sMode = 		Params::get( "sResultType", "" );
			$sMadeChanges = Params::get( "sMadeChanges", "" );
			
			$aMadeChanges = explode( ";", $sMadeChanges );
			foreach( $aMadeChanges as $sMadeChange )
			{
				$aMadeChange = explode( ",", $sMadeChange );
				
				if( !empty( $aMadeChange ) )
				{
					$nIDToChange = 	$aMadeChange[0];
					$sLevel = 		$aMadeChange[1];
					$nValue = 		$aMadeChange[2];
				}
				
				if( $sMode == "Profile" )
				{
					$nIDProfile = $nIDToChange;
				}
				if( $sMode == "Person" )
				{
					$nIDProfile = $oAccessAccounts->getIDProfile( $nIDToChange );
				}
				
				$nIDLevel = $oAccessLevel->getIDByName( $sLevel );
				
				if( $nIDProfile != 0 && $nIDLevel != 0 )
				{
					$oAccessLevelProfile->EditLevelForProfile( $nIDProfile, $nIDLevel, $nValue );
				}
			}
			
			$oResponse->setFormElement( "form1", "sMadeChanges", array( "value" => "" ) );
			
			$oResponse->printResponse();
		}
	}
?>