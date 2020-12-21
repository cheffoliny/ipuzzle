<?php

	class ApiSetupRequests
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oRequests = new DBRequests();
			$oRequests->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse("Задачи","requests");
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oRequests = new DBRequests();
			$oRequests->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>