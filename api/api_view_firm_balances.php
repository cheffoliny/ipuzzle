<?php
	class ApiViewFirmBalances {
		public function result( DBResponse $oResponse ) {
			$oSaldo 	= new DBSaldo();
			$aParams 	= Params::getAll();
			
			$oSaldo->getReport( $oResponse, $aParams );
			
			$oResponse->printResponse( "Салда", "firm_balances" );
		}
	}

?>