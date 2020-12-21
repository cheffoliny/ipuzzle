<?php
	class ApiObjectShifts {
		public function result( DBResponse $oResponse ) {
			global $db_sod;

			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) ) {
				$oShift = new DBObjectShifts();
				$aShift = $oShift->getReport( $nID, $oResponse );				
			}
			
			$oResponse->printResponse("Смени на обект", "object_shifts");
		}

		function delete( DBResponse $oResponse ) {
			global $db_sod;
			$nID = Params::get('nIDShift');
			$now = time();
			
			$oShifts = new DBObjectShifts();
		
			$validate = $oShifts->shiftInUse( $nID );
			
			if ( $validate['br'] > 0 ) {
				throw new Exception("Този вид смяна се използва в невалидиран график!!!", DBAPI_ERR_INVALID_PARAM);
			} else {
				//$oShifts->delete( $nID );
				$aData = array();
				$aData['id'] = $nID;
				$aData['validTo'] = $now;
				$aData['to_arc'] = 1;
			
				$oShifts->update( $aData );				
			}
			
			$oResponse->printResponse();
		}
	}
?>