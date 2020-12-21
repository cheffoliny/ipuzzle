<?php
	class ApiEmailInvoiceHistory {
				
		public function result( DBResponse $oResponse ) {
			$oMailInvoice = new DBMailInvoice();
			$aMailInvoice = array();
			
			//$nID = Params::get('nID', 0);

			$oMailInvoice->getHistory( $oResponse );
			
			$oResponse->printResponse();	
		}

	}
?>