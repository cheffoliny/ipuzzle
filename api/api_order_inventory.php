<?php
	class ApiOrderInventory {
		public function result( DBResponse $oResponse ) {
			$nIDOrder 	= Params::get("nID", 0);
			$sDocType 	= Params::get("sDocType", "");
			
			$oOrders = new DBOrders();
			
			$oOrders->getReportInventory( $oResponse, $nIDOrder, $sDocType );
			
			$oResponse->printResponse( 'Ордер - Опис', 'order_inventory' );
		}
		
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}		
		
		public function annulment( DBResponse $oResponse ) {
			global $db_name_system, $db_name_finance, $db_system, $db_finance;
			
			$nID 		= Params::get("nID", 0);
			$oOrders 	= new DBOrders();
			
			if ( $this->isValidID($nID) ) {
				$oOrders->annulment($oResponse, $nID);
			}			

			$oResponse->printResponse();
		}	
	}
?>