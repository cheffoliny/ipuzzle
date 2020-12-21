<?php
	class ApiShifthistory {
		public function result( DBResponse $oResponse ) {
			global $db_sod;

			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) ) {
				$oShift = new DBObjectShifts();
				$aShift = $oShift->getReportArchiv( $nID, $oResponse );				
			}
			
			$oResponse->printResponse("Смени на обект", "object_shifts");
		}

	}
?>