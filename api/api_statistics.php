<?php

	class ApiStatistics {
		
		public function load(DBResponse $oResponse) {
			
			$oDBStatistics = new DBStatistics();
			
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			//$aFilters = $oDBStatistics->getFilterNamesByIDPerson($nIDPerson);
			
			$aFilters = $oDBStatistics->getFiltersByIDPerson($nIDPerson);
			
			$oResponse->setFormElement('form1','nIDFilter');
			$oResponse->setFormElementChild('form1','nIDFilter',array("value" => ""),'---Изберете---');			
			foreach ($aFilters as $key => $value) {
				$oResponse->setFormElementChild('form1','nIDFilter',array("value"=>$key),$value);
			}
			
			$oResponse->setFormElement('form1','sFromDate',array(),date("d.m.Y"));
			$oResponse->setFormElement('form1','sToDate',array(),date("d.m.Y"));
			
			
			$oResponse->printResponse();
			
		}
		
		public function result(DBResponse $oResponse) {
			
			$aParams = Params::getAll();
			
			if(empty($aParams['nIDFilter'])) {
				throw new Exception("Изберете филтър");
			}
			
			$oDBStatistics = new DBStatistics();
			$oDBStatistics->getReport($oResponse,$aParams);
			
			$oResponse->printResponse('Статистики','statistic');
		}
		
	}

?>