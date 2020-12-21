<?php

	class ApiRegister
	{
	 	
		public function result( DBResponse $oResponse )
		{
			$nIDService = Params::get( "nIDService", 0 );
			$oDBRegister = new DBRegister();
			$oDBRegister->getReport( $oResponse, $nIDService );
			$oResponse->printResponse();
				
		}
			
					
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			if( $nID > 0 )
			{
				$oDBDelService = new DBServicesReceiptTree();
				$oDBDelService->deleteByID( $nID );
				$oResponse->printResponse();
			}
		}
		
			
	}
?>