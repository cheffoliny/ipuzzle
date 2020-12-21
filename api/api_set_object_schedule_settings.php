<?php
	class ApiSetObjectScheduleSettings {
		
		public function load( DBResponse $oResponse ) {

			$aSchedule = array();
			$oSchedule = new DBObjectScheduleSettings();
			
			$aSchedule = $oSchedule->getActiveSettings();
			
			if ( !empty($aSchedule) ) {
				$oResponse->setFormElement('form1', 'nFactor', 		array('value' => $aSchedule['factor']));
				$oResponse->setFormElement('form1', 'sNightFrom', 	array('value' => $aSchedule['night_from']));
				$oResponse->setFormElement('form1', 'sNightTo', 	array('value' => $aSchedule['night_to']));
			}
			
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID			= Params::get('nID', 0);
			$nFactor		= Params::get("nFactor");
			$sNightFrom		= Params::get("sNightFrom");
			$sNightTo		= Params::get("sNightTo");
			
			if ( empty($nFactor) ) {
				throw new Exception("Въведете коефицент за нощен труд!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sNightFrom) ) {
				throw new Exception("Въведете начало на нощна смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sNightTo) ) {
				throw new Exception("Въведете край на нощна смяна!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			$aData = array();
			$aData['id'] 			= $nID;
			$aData['factor'] 		= $nFactor;
			$aData['night_from'] 	= $sNightFrom;
			$aData['night_to'] 		= $sNightTo;
			
			$oSchedule = new DBObjectScheduleSettings();
			$oSchedule->update( $aData );
		}
	}
	
?>