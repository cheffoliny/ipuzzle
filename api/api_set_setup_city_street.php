<?php

	class ApiSetSetupCityStreet
	{
		public function get( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$nID = Params::get( "nID", 0 );
			
			$oCityStreets = new DBCityStreets();
			$oCities = new DBCities();
			
			//Set Cities
			$aCities = $oCities->getCities2();
			
			$oResponse->setFormElement( 'form1', 'nIDCity' );
			$oResponse->setFormElementChild( 'form1', 'nIDCity', array( "value" => 0 ), "--- Всички ---" );
			
			foreach( $aCities as $aCity )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDCity', array( "value" => $aCity['id'] ), $aCity['name'] );
			}
			
			if( isset( $aParams['nIDCityTrans'] ) && !empty( $aParams['nIDCityTrans'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDCity', 'value', $aParams['nIDCityTrans'] );
			}
			//End Set Cities
			
			if( !empty( $nID ) )
			{
				$aCityStreet = $oCityStreets->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aCityStreet['name'] ) );
				$oResponse->setFormElementAttribute( 'form1', 'nIDCity', 'value', $aCityStreet['id_city'] );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName = 	Params::get( "sName" );
			$nIDCity = 	Params::get( "nIDCity", 0 );
			
			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nIDCity ) )
				throw new Exception( "Изберете населено място!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] = Params::get( 'nID', 0 );
			$aData['name'] = $sName;
			$aData['id_city'] = $nIDCity;
			
			$oCityStreets = new DBCityStreets();
			$oCityStreets->update( $aData );
		}
	}

?>