<?php

	class ApiSetupCashiers
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oCashiers = new DBCashiers();
			$oCashiers->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Касиери", "cashiers", false );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oCashiers = new DBCashiers();
			$oCashiers->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>