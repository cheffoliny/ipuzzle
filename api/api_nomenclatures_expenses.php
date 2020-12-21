<?php

	class ApiNomenclaturesExpenses {
		
		public function result(DBResponse $oResponse) {
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$oDBNomenclaturesExpenses->getReport($oResponse);
			
			$oResponse->printResponse("Номенклатури разходи",'nomenclatures_expenses');
		}
		
		public function delete() {
			$nIDToDel = Params::get('id_to_del');
			
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$oDBNomenclaturesExpenses->delete($nIDToDel);
		}
	}

?>