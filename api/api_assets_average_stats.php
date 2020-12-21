<?php

	class ApiAssetsAverageStats
	{
		
		public function load( DBResponse $oResponse )
		{
			//Load Asset Groups
			$oAssetGroups = new DBAssetsGroups();
			
			$aGroups = $oAssetGroups->getGroupsAlphabetic();
			
			$oResponse->setFormElement( 'form1', 'nGroup' );
			$oResponse->setFormElementChild( 'form1', 'nGroup', array( "value" => 0 ), "-- Всички --" );
			
			foreach( $aGroups as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nGroup', array( "value" => $key ), $value );
			}
			//End Load Asset Groups
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oAssets = new DBAssets();
			$oAssets->getAverageStats( $aParams, $oResponse );
			
			$oResponse->printResponse( "Активи - Средни стойности", "assets_average_stats" );
		}
		
	}

?>