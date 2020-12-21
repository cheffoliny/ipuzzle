<?php
	class ApiObjectTroubles {
		public function result(DBResponse $oResponse) {
			global $db_sod;
			
			$nID = Params::get('nID', 0);
			$sTroubleType = Params::get('sTroubleType', 'all');
			$nService = Params::get('nService', 0);
			
			$aData = array();
			$aData['obj'] = $nID;
			$aData['type'] = $sTroubleType;
			$aData['service'] = $nService;
			
			
			$oTrouble = new DBTroubles();
			$oTrouble->getTroubles( $oResponse, $aData );
 
			$oResponse->printResponse(); 
		}

		function delete( DBResponse $oResponse ) {
			$nID = Params::get('nIDTrouble', 0);

			$oTrouble = new DBTroubles();
			$oTrouble->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>