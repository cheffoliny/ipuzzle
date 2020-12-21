<?php

	class ApiObjectStorePPP
	{
		public function result( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			
			if( !empty( $nID ) )
			{ 
				$oPPP = new DBPPP();
				$oPPP->getPPPByObject( $nID, $oResponse );
			}
			
			$oResponse->printResponse(); 
		}
	}

?>