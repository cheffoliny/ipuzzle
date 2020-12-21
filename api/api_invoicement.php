<?php
	class ApiInvoicement {
				
		public function result( DBResponse $oResponse ) {
			$oInvoice = new DBInvoicement();
			$aInvoice = array();

			$aInvoice = $oInvoice->makeInvoice( $oResponse );
			
			$oResponse->printResponse();	
		}

		function record( DBResponse $oResponse ) {

			$oObject = new DBHCObjects();
			$aObject = array();
			
			$date_to = Params::get('date_to', '');
			$aData = !empty($date_to) ? jsDateToTimestamp($date_to) : 0;			
			
			$oObject->createRecord( $aData, $oResponse );
			
			$oResponse->printResponse();
		}

	}
?>
