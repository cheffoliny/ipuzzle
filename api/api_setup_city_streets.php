<?php

	class ApiSetupCityStreets
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
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
			
			if( isset( $aParams['nIDCity'] ) && !empty( $aParams['nIDCity'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDCity', 'value', $aParams['nIDCity'] );
			}
			//End Set Cities
			
			$oCityStreets->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Улици", "city_streets" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oCityStreets = new DBCityStreets();
			$oCityStreets->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>