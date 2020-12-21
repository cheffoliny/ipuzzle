<?php

	class ApiSetupCompensations
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oCompensations = new DBCompensations();
			$oCompensations->getReport($aParams, $oResponse);
			 
			$oResponse->printResponse("Отговорности","compensetions");
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oCompensations = new DBCompensations();
			$oCompensations->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>