<?php
	class ApiTechSettings
	{
		public function result( DBResponse $oResponse )
		{
			$oTechSettings = new DBTechSettings();
			$oTechSettings->getReport( $oResponse );				
			
			$oResponse->printResponse( "Настройки на техници", "tech_settings" );
		}

		function delete( DBResponse $oResponse )
		{
			$nID = Params::get('nID');
			
			$oTechSettings = new DBTechSettings();
			$oTechSettings->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>