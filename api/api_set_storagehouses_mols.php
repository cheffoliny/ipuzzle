<?php
	
	class ApiSetStoragehousesMols
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oFirms = 				new DBFirms();
			$oOffices = 			new DBOffices();
			$oPersonnel = 			new DBPersonnel();
			$oStoragehousesMols = 	new DBStoragehousesMols();
			
			//Load Firms
			$aFirms = $oFirms->getFirms2();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => 0 ), "--Изберете--" );
			
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => $aFirm['id'] ), $aFirm['name'] );
			}
			//End Load Firms
			
			//Init Offices
			$oResponse->setFormElement( 'form1', 'nIDOffice' );
			$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => 0 ), "--Изберете--" );
			//End Init Offices
			
			//Init Persons
			$aPersons = $oStoragehousesMols->getAllPersons( $nID );
			
			$oResponse->setFormElement( 'form1', 'sel_persons' );
			$aPersonList = array();
			foreach( $aPersons as $aPerson )
			{
				$oResponse->setFormElementChild( 'form1', 'sel_persons', array( "value" => $aPerson['id'] ), $aPerson['name'] );
				$aPersonList[] = $aPerson['id'];
			}
			
			if( !empty( $aPersonList ) )
			{
				$oResponse->setFormElement( 'form1', 'sPersonList', array( "value" => implode( ",", $aPersonList ) ), implode( ",", $aPersonList ) );
			}
			//End Init Persons
			
			$oResponse->printResponse();
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nFirm = Params::get( 'nIDFirm', 0 );
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			
			if( !empty( $nFirm ) )
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm( $nFirm );
				
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => '0' ), "--Изберете--" );
				foreach( $aOffices as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => $key ), $value );
				}
			}
			else
			{
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => '0' ), "Първо изберете фирма" );
			}
			
			$oResponse->setFormElement( 'form1', 'nIDPerson', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDPerson', array( "value" => '0' ), "Първо изберете регион" );
			
			$oResponse->printResponse();
		}
		
		public function loadPersons( DBResponse $oResponse )
		{
			$nOffice = 		Params::get( 'nIDOffice' );
			$nID =			Params::get( 'nID', 0 );
			$sPersonList = 	Params::get( 'sPersonList' );
			
			$oDBPersonnel = new DBPersonnel();
			$oStoragehouse = new DBStoragehouses();
			
			//Невалидни потребители са вече добавените и титулярно МОЛ
			$aMOL = $oStoragehouse->getMOL( $nID );
			$aPersonList = split( ",", $sPersonList );
			
			$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice3( $nOffice );
			$oResponse->setFormElement( 'form1', 'all_persons' );
			
			if( empty( $aResponsible ) )
			{
				$oResponse->setFormElementChild( 'form1', 'all_persons', array( "value" => 0 ), '-- Няма намерени служители --' );
			}
			else
			{
				foreach( $aResponsible as $key => $value )
				{
					$nBanned = false;
					
					foreach( $aPersonList as $nValue )
					{
						if( $key == $nValue )$nBanned = true;
					}
					if( $key == $aMOL['mol_id'] )$nBanned = true;
					
					if( !$nBanned )$oResponse->setFormElementChild( 'form1', 'all_persons', array( "value" => $key ), $value );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$oStoragehouseMols = new DBStoragehousesMols();
			
			$nID 			= Params::get( "nID", 0 );
			$sPersonList 	= Params::get( "sPersonList" );
			
			$oStoragehouseMols->updateMols( $nID, $sPersonList );
			
			$oResponse->printResponse();
		}
	}
?>