<?php
	class ApiSetupMeasures
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oMeasures = new DBMeasures();
			$oMeasures->getReport($aParams, $oResponse);
			 
			$oResponse->printResponse("Мерни единци","measures");
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oMeasures = new DBMeasures();
			$oMeasures->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>