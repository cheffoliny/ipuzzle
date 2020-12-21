<?php

	class ApiAssetsEnter {
		
		public function load( DBResponse $oResponse) {
			
//			$aOutput = array();
//			exec('dir',$aOutput);
//			APILog::Log(0,$aOutput);
			
			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )	{
			
			$aParams = Params::getAll();
			
			$oAssetsPPPs = new DBAssetsPPPs();
			$oAssetsPPPs -> getByType($aParams,$oResponse,'enter');
			
			$oResponse->printResponse( "Активи - Придобиване", "assets_enter" );
		}
		
	}


?>