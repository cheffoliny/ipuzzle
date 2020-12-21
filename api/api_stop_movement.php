<?php

	class ApiStopMovement {
		
		public function load(DBResponse $oResponse) {
			
			$oResponse->setFormElement('form1','sEndTime',array(),date("d.m.Y"));
			$oResponse->setFormElement('form1','sReasonTime',array(),date("d.m.Y"));

			$oResponse->printResponse();
		}
		
		public function save() {
			
			$nID = Params::get("nID", 0);
			$sNote = Params::get("sNote", '');
			
			$sEndTime = jsDateToTimestamp( Params::get("sEndTime", '0000-00-00') );
			$sReasonTime = jsDateToTimestamp( Params::get("sReasonTime", '0000-00-00') );
			
			$sEndTimeH = Params::get("sEndTimeH", '');
			$sReasonTimeH = Params::get("sReasonTimeH", '');

			
			if ( !empty($sEndTimeH) ) {
				$sEndTime = date("Y-m-d", $sEndTime)." ".$sEndTimeH;
				$aData['end_time'] = $sEndTime;
			} else {
				//throw new Exception('Въведете време на пристигане');
			}

			if ( !empty($sReasonTimeH) ) {
				$sReasonTime = date("Y-m-d", $sReasonTime)." ".$sReasonTimeH;
				$aData['reason_time'] = $sReasonTime;
			} else {
				//throw new Exception('Въведете време на освобождаване');
			}

			$oDBWorkCardMovement = new DBWorkCardMovement();
			
			$aData['id'] = $nID;
			$aData['note'] = $sNote;
			
			$oDBWorkCardMovement -> update($aData);
		}
		
	}

?>