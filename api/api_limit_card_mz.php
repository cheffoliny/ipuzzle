<?php

	class ApiLimitCardMz
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oPPP = new DBPPP();
			$oPPP->getReportMz($aParams, $oResponse);
			
			$oResponse->printResponse();
		}
	}

?>