<?php
	class ApiStoragehouses
	{
		
		public function load( DBResponse $oResponse )
		{
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => '0' ) ), "--Изберете--" );
			foreach( $aFirms as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => $key ) ), $value );
			}
			
			//$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			//$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
			
			$aParams = Params::getAll();
			if( !isset( $aParams['nIDFirm'] ) && !isset( $aParams['nIDOffice'] ) )
			{
				$oOffices = new DBOffices();
				$oOffices->retrieveLoggedUserOffice( 'nIDFirm', 'nIDOffice', $oResponse, 0, 1, "--Всички--" );
			}
			
			$oResponse->printResponse();
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nFirm = Params::get( 'nIDFirm' );
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			
			if( !empty( $nFirm ) )
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm( $nFirm );
				
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
		
		function result( DBResponse $oResponse )
		{
			$nIDFirm 	= Params::get( 'nIDFirm', 0 );
			$nIDOffice 	= Params::get( 'nIDOffice', 0 );
			$sType 		= Params::get( 'sType', '' );
			
			if( empty( $nIDFirm ) )
			{
				throw new Exception( "Изберете фирма!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$oDBStoragehouses = new DBStoragehouses();
			$oDBStoragehouses->getReport( $nIDOffice, $nIDFirm, $sType, $oResponse );
			
			$oResponse->printResponse( "Складове", "storagehouses" );
		}
		
		function delete( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID' );
			
			$oDBStoragehouses	= new DBStoragehouses();
			$oDBStates			= new DBStates();
			$aResult			= $oDBStates->getCountNomenclaturesByStoragehouseID($nID);
			
			if (empty($aResult['count'])) {
				$oDBStoragehouses->delete( $nID );
			}
				else {
					$aResult['count'] = round($aResult['count']);
					if ($aResult['count']==1) throw new Exception ("Не може да премахнете този склад! \nВ него има в наличност {$aResult['count']} номенклатура!");
						else throw new Exception ("Не може да премахнете този склад! \nВ него има в наличност {$aResult['count']} номенклатури!");
				}
					
			$oResponse->printResponse();
		}
	}
?>