<?php
	class ApiAssetsSettings
	{
		public function result( DBResponse $oResponse )
		{
			$oAssetsSettings = new DBAssetsSettings();
			$oAssetsSettings->getReport( $oResponse );				
			
			APILog::Log(0,$_SESSION);
			
			$oResponse->printResponse( "Настройки Активи", "assets_settings" );
		}

		function delete( DBResponse $oResponse )
		{
			$nID = Params::get('nID');
			
			$oAssetsSettings = new DBAssetsSettings();
			$oAssetsSettings->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>