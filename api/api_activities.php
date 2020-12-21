<?php

	class ApiActivities
	{
	 	
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oDBActivities = new DBActivitiesOperations();
			$oDBActivities->getActivies( $oResponse, $aParams );
			$oResponse->printResponse();
				
		}
			
					
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			$oDBActivities = new DBActivitiesOperations();
			$oDBActivities->delete( $nID );
			$oResponse->printResponse();
		}
		
			
	}
?>