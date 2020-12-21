<?php
	class ApiLimitCardOperations {
		public function result( DBResponse $oResponse ) {
			$nIDLimitCard = Params::get('nID', 0);
			
			$oOperations = new DBLimitCardOperations();
			$oOperations->getReportByLC( $nIDLimitCard, $oResponse );
			
			$oResponse->printResponse();
		}

		public function delete( DBResponse $oResponse ) {
			$nID = Params::get('nIDOperation', 0);
			
			$oOperations = new DBLimitCardOperations();
			$oOperations->delete( $nID );
			
			$oResponse->printResponse();
		}

	}

?>