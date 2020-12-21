<?php
	class ApiObjectSupport {
		public function result(DBResponse $oResponse) {
			global $db_sod;
			
			$nID = Params::get('nID', 0);
			$nService = Params::get('nService', 0);
			
			$aData = array();
			$aData['obj'] = $nID;
			$aData['service'] = $nService;
			
			
			$oSupport = new DBTechLimitCards();
			$oSupport->getReportObject( $aData, $oResponse );
 
			$oResponse->printResponse(); 
		}

		function delete( DBResponse $oResponse ) {
			$nID = Params::get('nIDSupport', 0);

			$oSupport = new DBTechSupport();
			$oSupport->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>