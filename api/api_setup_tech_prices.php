<?php

	class ApiSetupTechPrices
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oTechPrices = new DBTechPrices();
			$oTechPrices->getReport( $aParams, $oResponse );
			 
			$oResponse->printResponse( "Техника", "tech_prices" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oTechPrices = new DBTechPrices();
			$oTechPrices->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>