<?php

	class ApiSetupPPP
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			APILog::Log(0,$aParams['sToDate']);
			
			$oPPP = new DBPPP();
			$oPPP->getReport2( $aParams, $oResponse );
			
			$oResponse->printResponse( "Приемо-Предаване", "ppp" );
		}
		
		public function setDefaults( DBResponse $oResponse )
		{
			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );

			$oResponse->printResponse();
		}
	}

?>