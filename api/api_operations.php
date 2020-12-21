<?php

	class ApiOperations
	{
	 	
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oDBOperations = new DBActivitiesOperations();
			$oDBOperations->getOperations( $oResponse, $aParams );
			$oResponse->printResponse();
				
		}
			
					
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			$oDBOperations = new DBActivitiesOperations();
			$oDBOperations->delete( $nID );
			$oResponse->printResponse();
		}
		
			
	}
?>