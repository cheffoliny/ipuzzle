<?php
	class ApiPersonShifts {
		function result( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			 
			$oShifts = new DBPersonShifts();
			$oShifts->getReport($aParams, $oResponse);
				
			$oResponse->printResponse("Номенклатури - Смени", "person_shifts");
		}

		function delete( DBResponse $oResponse ) {
			global $db_personnel;
			$nID = Params::get('nID');
			
			$oShifts = new DBPersonShifts();
			$oShifts->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
	
?>