<?php

	class ApiTechOperationsScheme
	{
		public function result( DBResponse $oResponse )
		{
			$oTechOperations = new DBTechOperations();
			
			$oTechOperations->getReport( $oResponse );
			
			$oResponse->printResponse( "Операции", "tech_operations" );
		}
	}

?>