<?php

	class ApiAssetsStockTaking
	{
		public function refreshPersons( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oOffices = new DBOffices();
			$oPersonnel = new DBPersonnel();
			
			//Set Persons
			if( !isset( $aParams['nIDOffice'] ) )$aParams['nIDOffice'] = 0;
			
			if( $aParams['nIDOffice'] )
			{
				$aPersons = $oPersonnel->getPersonnelsByIDOffice2( $aParams['nIDOffice'] );
				
				$oResponse->setFormElement( 'form1', 'nIDPerson' );
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => 0 ), "--- Всички ---" );
				foreach( $aPersons as $aPerson )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => $aPerson['id'] ), $aPerson['name'] );
				}
				
				if( isset( $aParams['nIDPerson'] ) )
					$oResponse->setFormElementAttribute( 'form1', 'nIDPerson', 'value', $aParams['nIDPerson'] );
			}
			else
			{
				$oResponse->setFormElement( 'form1', 'nIDPerson' );
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => 0 ), "Първо изберете Регион" );
			}
			//End Set Persons
			
			$oResponse->printResponse();
		}
		
		public function refreshOffices( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oFirms = new DBFirms();
			$oOffices = new DBOffices();
			
			//Set Offices
			if( !isset( $aParams['nIDFirm'] ) )$aParams['nIDFirm'] = 0;
			
			if( $aParams['nIDFirm'] )
			{
				$aOffices = $oOffices->getOfficesByFirm( $aParams['nIDFirm'] );
				
				$oResponse->setFormElement( 'form1', 'nIDOffice' );
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => 0 ), "--- Всички ---" );
				foreach( $aOffices as $aOffice )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => $aOffice['id'] ), $aOffice['name'] );
				}
			}
			else
			{
				$oResponse->setFormElement( 'form1', 'nIDOffice' );
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => 0 ), "Първо изберете Фирма" );
			}
			//End Set Offices
			
			//Set Persons
				$oResponse->setFormElement( 'form1', 'nIDPerson' );
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => 0 ), "Първо изберете Регион" );
			//End Set Persons
			
			$oResponse->printResponse();
		}
		
		public function load( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oFirms = new DBFirms();
			$oOffices = new DBOffices();
			$oPersonnel = new DBPersonnel();
			$oStoragehouses = new DBAssetsStoragehouses();
			
			//Set Firms
			$aFirms = $oFirms->getFirms2();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => 0 ), "--- Всички ---" );
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => $aFirm['id'] ), $aFirm['name'] );
			}
			
			if( isset( $aParams['nIDFirm'] ) )
				$oResponse->setFormElementAttribute( 'form1', 'nIDFirm', 'value', $aParams['nIDFirm'] );
			//End Set Firms
			
			$oOffices->retrieveLoggedUserOffice( 'nIDFirm', 'nIDOffice', $oResponse, 0, 1, "--- Всички ---" );
			
			//Init Persons
			$nIDUserOffice = $_SESSION['userdata']['id_office'];
			
			$oResponse->setFormElement( 'form1', 'nIDPerson' );
			$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => 0 ), "--- Всички ---" );
			
			if( $nIDUserOffice )
			{
				$aPersons = $oPersonnel->getPersonnelsByIDOffice2( $nIDUserOffice );
				
				foreach( $aPersons as $aPerson )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => $aPerson['id'] ), $aPerson['name'] );
				}
				
				if( isset( $aParams['nIDPerson'] ) )
					$oResponse->setFormElementAttribute( 'form1', 'nIDPerson', 'value', $aParams['nIDPerson'] );
			}
			//End Init Persons
			
			//Set Storagehouses
			$aStoragehouses = $oStoragehouses->getAssetsStoragehouses2();
			
			$oResponse->setFormElement( 'form1', 'nIDStoragehouse' );
			$oResponse->setFormElementChild( 'form1', 'nIDStoragehouse', array( "value" => 0 ), "--- Всички ---" );
			
			foreach( $aStoragehouses as $aStoragehouse )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDStoragehouse', array( "value" => $aStoragehouse['id'] ), $aStoragehouse['name'] );
			}
			
			if( isset( $aParams['nIDStoragehouse'] ) )
				$oResponse->setFormElementAttribute( 'form1', 'nIDStoragehouse', 'value', $aParams['nIDStoragehouse'] );
			//End Set Storagehouses
			
			//Set Groups
			$oAssetsGroups = new DBAssetsGroups();
			
			$oResponse->setFormElement( 'form1', 'nIDGroup', array(), "" );
			$oResponse->setFormElementChild( 'form1', 'nIDGroup', array( "value" => 0 ), "--- Всички ---" );
			$aRoots = $oAssetsGroups->getRootGroups();
			if( !empty( $aRoots ) )
			{
				foreach( $aRoots as $aRoot )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDGroup', array( "value" => $aRoot['id'] ), $aRoot['name'] );
					$this->getEachGroupChild( 'nIDGroup', 0, $aRoot['id'], $oResponse );
				}
			}
			//End Set Groups
			
			//Set Nomenclatures
			$oResponse->setFormElement( 'form1', 'nIDNomenclature', array(), "" );
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => 0 ), "--- Всички ---" );
			//End Set Nomenclatures
			
			$oResponse->printResponse();
		}
		
		public function getEachGroupChild( $sFormField, $nLevel, $nIDGroup, DBResponse $oResponse )
		{
			$oAssetsGroups = new DBAssetsGroups();
			
			$sLevel = "";
			for( $i = 0; $i <= $nLevel; $i++ )
			{
				$sLevel .= "    ";
			}
			
			$aChildren = $oAssetsGroups->getChilds( $nIDGroup );
			if( !empty( $aChildren ) )
			{
				foreach( $aChildren as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', $sFormField, array( "value" => $key ), $sLevel . $value );
					$this->getEachGroupChild( $sFormField, $nLevel + 1, $key, $oResponse );
				}
			}
		}
		
		public function refreshNomenclatures( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset( $aParams['nIDGroup'] ) )
			//Set Nomenclatures
			$oAssetsNomenclatures = new DBAssetsNomenclatures();
			
			$aAssetsNomenclatures = $oAssetsNomenclatures->getNomenclaturesByGroup( (int) $aParams['nIDGroup'] );
			
			$oResponse->setFormElement( 'form1', 'nIDNomenclature', array(), "" );
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => 0 ), "--- Всички ---" );
			foreach( $aAssetsNomenclatures as $key => $value )
			{
				$oResponse->setFormElementChild( "form1", 'nIDNomenclature', array( "value" => $key ), $value );
			}
			//End Set Nomenclatures
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset ( $aParams['api_action'] ) && ( $aParams['api_action'] == "export_to_pdf" || $aParams['api_action'] == "export_to_xls" ) )
			{
				$aParams['is_paging_ignored'] = 1;
			}
			else
			{
				$aParams['is_paging_ignored'] = 0;
			}
			
			$oAssets = new DBAssets();
			
			if( $aParams['sResultType'] == "general" || $aParams['sResultType'] == "detailed" )
			{
				$oAssets->getReport( $aParams, $oResponse );
			}
			if( $aParams['sResultType'] == "groups" || $aParams['sResultType'] == "subgroups" )
			{
				$oAssets->getGroupsReport( $aParams, $oResponse );
			}
			if( $aParams['sResultType'] == "nomenclatures" )
			{
				$oAssets->getNomenclaturesReport( $aParams, $oResponse );
			}
			
			$oResponse->setFormElement( "form1", "nIDCustomGroup", 			array( "value" => 0 ), 0 );
			$oResponse->setFormElement( "form1", "nIDCustomNomenclature", 	array( "value" => 0 ), 0 );
			
			$oResponse->printResponse( "Активи - Инвентаризация", "assets_stock_taking", false );
		}
	}

?>