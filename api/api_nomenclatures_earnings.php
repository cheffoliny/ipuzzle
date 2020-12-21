<?php
	
	class ApiNomenclaturesEarnings {
		
		public function result(DBResponse $oResponse) {
			
			$oDBNomenclaturesEarnings = new DBNomenclaturesEarnings();
			$oDBNomenclaturesEarnings->getReport($oResponse);
			
			$oResponse->printResponse("Номенклатури приходи","nomenclatures_earnings");
		}
		
		public function delete() {
			$nIDToDel = Params::get('id_to_del','0');
			
			$oDBNomenclaturesEarning = new DBNomenclaturesEarnings();
			$oDBNomenclaturesEarning->delete($nIDToDel);
		}
	}
?>