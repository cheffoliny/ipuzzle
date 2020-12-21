<?php

	class ApiStatesStatistics {
		
		public function load(DBResponse $oResponse) {
			
			$oDBStatesFilters = new DBStatesFilters();
					
			$nIDPerson = $_SESSION['userdata']['id_person'];
				
			$aFilters = $oDBStatesFilters->getAutoFiltersByIDPerson($nIDPerson);
			
			$oResponse->setFormElement('form1','nIDFilter',array());
			$oResponse->setFormElementChild('form1','nIDFilter',array("value" => '0'),"---Изберете---");
			
			foreach ($aFilters as $key => $value) {
				$oResponse->setFormElementChild('form1','nIDFilter',array("value" => $key),$value);
			}

			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			
			$oResponse->printResponse();
		}
		
		public function result(DBResponse $oResponse) {
			
			$nIDFilter	= Params::get('nIDFilter','0');
			$sDateFrom	= Params::get('sFromDate','');
			$sDateTo	= Params::get('sToDate','');
 			
			//throw new Exception($sDateFrom);
			
			if(empty($nIDFilter)) {
				throw new Exception('Моля изберете филтър');
			}
			
			
			$oDBStatesStatistics = new DBStatesStatistics();
			
			$nIDPerson = $_SESSION['userdata']['id_person'];
			$nDateFrom = jsDateToTimestamp($sDateFrom);
			$nDateTo = jsDateToTimestamp($sDateTo);
			if(!empty($nDateTo)) {
				$nDateTo += 24 * 60 * 60;
			}
			
			$nDateTo ;
			
			$aData = array();
			$aData['nIDPerson'] = $nIDPerson;
			$aData['nIDFilter'] = $nIDFilter;
			$aData['nDateFrom'] = $nDateFrom;
			$aData['nDateTo'] = $nDateTo;
			
			
			$oDBStatesStatistics->getReport($oResponse,$aData);
			
			$oResponse->printResponse();
			
		}
	}

?>