<?php

	class ApiSetupConcessions
	{
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBConcession = new DBConcession();
			$oDBConcession->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Отстъпки", "setup_concessions", true );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBConcession = new DBConcession();
			
			$nID = isset( $aParams['id'] ) ? $aParams['id'] : 0;
			
			if( !empty( $nID ) )
			{
				$oDBConcession->delete( $nID );
			}
			
			$oResponse->printResponse();
		}
	}

?>