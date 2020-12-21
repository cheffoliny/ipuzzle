<?php
	class ApiBudgetArchive {
		public function result( DBResponse $oResponse ) {
			$oBudget = new DBBudget();
			
			$oBudget->getReport( $oResponse );
			
			$oResponse->printResponse( "Буджети", "budgets" );
		}	
		
		public function delete() {
			
			$nIDBudget	= Params::get("nIDBudget", 0);
			
			$oBudget 	= new DBBudget();
			
			$oBudget->deleteBudget($nIDBudget);
		}			
	}
?>