<?php

class DBObjectsServicesSignalsCanvas extends DBBase2 {

	function __construct() {
		global $db_sod;
		parent::__construct($db_sod,'objects_services_signals');
	}
	
	function getSignalsForNotifications() {
		
		global $db_name_finance;
		
		$sQuery = "
			SELECT
				os.id_object,
				oss.id_signal,
				oss.target_gsm,
				oss.service_code,
				o.id_office,
				s.client_description,
				os.id_service,
				ns.single_price
			FROM objects_services_signals oss			
			JOIN signals s ON s.id = oss.id_signal
			JOIN objects_services os ON os.id = oss.id_object_service
			JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = os.id_service
			JOIN objects o ON o.id = os.id_object
			WHERE oss.service_code IN ('SMS','SMS_AB','TEL','TEL_AB')
				AND os.start_date <= '".date('Y-m-d')."'
				AND o.id_status != 4
		";

		$aData = $this->select($sQuery);
		
		$aFinalData = array('SMS' => array(),'TEL' =>array());
		
		if(!empty($aData)) {
			foreach($aData as $value) {
				if(in_array($value['service_code'],array('SMS','SMS_AB'))) {
					//$aFinalData['SMS'][$value['id_object']][$value['id_signal']] = $value;
					$aFinalData['SMS'][$value['id_object']][$value['id_signal']][$value['target_gsm']] = $value;												
				}
				if(in_array($value['service_code'],array('TEL','TEL_AB'))) {
					//$aFinalData['TEL'][$value['id_object']][$value['id_signal']] = $value;	
					$aFinalData['TEL'][$value['id_object']][$value['id_signal']][$value['target_gsm']] = $value;					
				}
			}
		}
		
		return $aFinalData;		
	}
	
	function getByIdObjectService($nIDService) {
		
		$sQuery = "
				SELECT
					*
				FROM objects_services_signals
				WHERE id_object_service = {$nIDService}
			";

		return $this->select($sQuery);
	}
	
	function delByIDObjectService($nIDService) {
		
		$nIDService = (int) $nIDService;
		if(empty($nIDService)) return false;
		
		$sQuery = "
			DELETE 
			FROM objects_services_signals
			WHERE id_object_service = {$nIDService}
		";
			
		$this->oDB->Execute($sQuery);
		
	}
	
}