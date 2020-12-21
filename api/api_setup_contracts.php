<?php

	class ApiSetupContracts {
		
		public function result( DBResponse $oResponse ) {
			
			$oCharges = new DBSetupContracts();
			$oCharges->getReport( $oResponse );
			 
			$oResponse->printResponse("Настройки", "setup_contracts");
		}
		
		public function delete( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			
			$oCharges = new DBSetupContracts();
			$oCharges->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>