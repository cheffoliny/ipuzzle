<?php

	class ApiObjectStoreState
	{
		public function result( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			
			if( !empty( $nID ) )
			{
				$oPPP = new DBPPP();
				$nRowCount  = $_SESSION['userdata']['row_limit'];
				$_SESSION['userdata']['row_limit'] = 200;
				$oPPP->getReportByObject( $nID, $oResponse );
				$_SESSION['userdata']['row_limit'] = $nRowCount;
			}
			
			$oResponse->printResponse();
		}
	}

?>