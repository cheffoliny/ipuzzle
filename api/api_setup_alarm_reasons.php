<?php

	class ApiSetupAlarmReasons
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oReasons = new DBAlarmReasons();
			$oReasons->getReport($aParams, $oResponse);
			 
			 $oResponse->printResponse("Причини за аларми","alarm_reasons");
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			$oReasons = new DBAlarmReasons();
			$oReasons->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>