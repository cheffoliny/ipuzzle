<?php

	class ApiNomenclaturesServices {
		
		public function result(DBResponse $oResponse) {
			
			$oDBNomenclaturesServices = new DBNomenclaturesServices();
			$oDBNomenclaturesServices->getReport($oResponse);
			
			$oResponse->printResponse("Номенклатури услуги",'nomenclatures_services');
		}
		
		public function delete() {
			
			$nIDToDel = Params::get('id_to_del',0);
			
			$oDBNomenclaturesServices = new DBNomenclaturesServices();
			$oDBNomenclaturesServices->delete($nIDToDel);
		}
	}

?>