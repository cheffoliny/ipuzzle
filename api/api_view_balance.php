<?php
	class ApiViewBalance {
		public function result( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oOrders = new DBOrders();
			$oOrders->getBalanceReport( $oResponse, $aParams );
			
			$oResponse->printResponse( "Наличности", "view_balance" );
		}
	}
?>
