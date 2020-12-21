<?php

	class ApiTechTiming
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oTechTiming = new DBTechTiming();
			$oTechTiming->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Технически обслужвания", "tech_timing" );
		}
	}

?>