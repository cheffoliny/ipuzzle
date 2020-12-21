<?php

	class ApiSetupCities
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oCities = new DBCities();
			$oCities->getReport( $aParams, $oResponse );
			 
			$oResponse->printResponse( "Населени места", "cities" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oCities = new DBCities();
			$oCities->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>