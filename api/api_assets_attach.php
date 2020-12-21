<?php

	class ApiAssetsAttach {
		
		public function load( DBResponse $oResponse) {
			
			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )	{
			
			$aParams = Params::getAll();
			
			$oAssetsPPPs = new DBAssetsPPPs();
			$oAssetsPPPs -> getByType($aParams,$oResponse,'attach');
			
			$oResponse->printResponse( "Активи - Въвеждане", "assets_attach" );
		}
		
	}


?>