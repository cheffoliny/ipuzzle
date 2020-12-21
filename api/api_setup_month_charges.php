<?php

	class ApiSetupMonthCharges
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oCharges = new DBContractMonthCharges();
			$oCharges->getReport($aParams, $oResponse);
			 
			$oResponse->printResponse("Такси","month_charges");
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oCharges = new DBContractMonthCharges();
			$oCharges->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>