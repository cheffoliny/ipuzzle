<?php

	class ApiSetNomenclaturesEarexpFirms {
		
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			$nIDFirm = Params::get('nIDFirm',0);
			
			if(empty($nIDFirm)) {
				$nIDFirm = $nID;
			}
			
			$oDBFirms = new DBFirms();
			
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement('form1','nIDFirm');
			foreach ($aFirms as $key => $value) {
				if($key == $nIDFirm) {
					$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key,"selected" => "selected"),$value);
				} else {
					$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key),$value);
				}
			}
			
			$oDBNomenclaturesEarnings = new DBNomenclaturesEarnings();
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$oDBNomenclaturesEarexpFirms = new DBNomenclaturesEarexpFirms();
			
			$aNomenclaturesEarnings = $oDBNomenclaturesEarnings->getAllAssoc();
			$aNomenclaturesExpenses = $oDBNomenclaturesExpenses->getNomenclaturesExpenses();

			$aNomenclaturesEarningsFirm = $oDBNomenclaturesEarexpFirms->getEarningsByIDFirmAssoc($nIDFirm);
			$aNomenclaturesExpensesFirm = $oDBNomenclaturesEarexpFirms->getExpensesByIDFirmAssoc($nIDFirm);
			
			
			$oResponse->setFormElement('form1','all_earnings');
			$oResponse->setFormElement('form1','account_earnings');
			$oResponse->setFormElement('form1','all_expenses');
			$oResponse->setFormElement('form1','account_expenses');
			
			foreach ($aNomenclaturesEarnings as $key => $value) {
				if(in_array($key,$aNomenclaturesEarningsFirm)) {
					$oResponse->setFormElementChild('form1','account_earnings',array("value" => $key,"selected"=>"selected"),$value);	
				} else {
					$oResponse->setFormElementChild('form1','all_earnings',array("value" => $key),$value);	
				}
			}
			
			foreach ($aNomenclaturesExpenses as $key => $value) {
				if(in_array($key,$aNomenclaturesExpensesFirm)) {
					$oResponse->setFormElementChild('form1','account_expenses',array("value" => $key,"selected"=>"selected"),$value);
				} else {
					$oResponse->setFormElementChild('form1','all_expenses',array("value" => $key),$value);
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			$nIDFirm = Params::get('nIDFirm',0);
			
			$aAccountEarnings = Params::get('account_earnings',array());
			$aAccountExpenses = Params::get('account_expenses',array());
			
			$oDBNomenclaturesEarexpFirms = new DBNomenclaturesEarexpFirms();
			
			$oDBNomenclaturesEarexpFirms->delByIDFirm($nIDFirm);
			
			if(!empty($aAccountEarnings)) {
				foreach ($aAccountEarnings as $value) {
					$aData = array();
					$aData['id_nomenclature_earexp'] = $value;
					$aData['nomenclature_type'] = 'earning';
					$aData['id_firm'] = $nIDFirm;
					
					$oDBNomenclaturesEarexpFirms->update($aData);
				}
			}
			
			if(!empty($aAccountExpenses)) {
				foreach ($aAccountExpenses as $value) {
					$aData = array();
					$aData['id_nomenclature_earexp'] = $value;
					$aData['nomenclature_type'] = 'expense';
					$aData['id_firm'] = $nIDFirm;
					
					$oDBNomenclaturesEarexpFirms->update($aData);
				}
			}
		}
		
	}

?>