<?php

	class ApiDayShifts
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjectShifts = new DBObjectShifts();
			$oObjectShifts->getObjectShiftsGraph( $oResponse );
			
			$oResponse->printResponse("Смени","dayshifts");
		}
		
	}
?>