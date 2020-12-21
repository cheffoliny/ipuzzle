<?php

	class ApiAssetsWaste {
		
		public function load( DBResponse $oResponse) {
			
			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )	{
			
			$aParams = Params::getAll();
			
			$oAssetsPPPs = new DBAssetsPPPs();
			$oAssetsPPPs -> getByType($aParams,$oResponse,'waste');
			
			$oResponse->printResponse( "Активи - Бракуване", "assets_waste" );
		}
		
	}


?>