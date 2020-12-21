<?php 

	class ApiPersonActives
	{
	 	 function result( DBResponse $oResponse )
		 {
		 	$id = 		Params::get( 'id', 0 );
		 	$sMyType = 	Params::get( 'sMyType', 'full' );
		 	
			$oDBAssetsActives= new DBAssets();
			//APILog::Log( 0, $id );
			
			$oDBAssetsActives->getActivesByID( $oResponse, $id, $sMyType );
			
			$oResponse->printResponse();
		 }
	}

?>