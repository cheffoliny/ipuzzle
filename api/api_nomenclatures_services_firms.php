<?php

	class ApiNomenclaturesServicesFirms {
		
		public function result(DBResponse $oResponse) {
			$nIDFirm = Params::get('nIDFirm',0);
			
			$oDBFirms = new DBFirms();
			
			$aFirms = $oDBFirms->getFirms4();
			
			if(empty($nIDFirm)) {
				$oDBOffices = new DBOffices();
				$nIDFirm = $oDBOffices->getFirmByIDOffice($_SESSION['userdata']['id_office']);
			}
			
			$oResponse->setFormElement('form1','nIDFirm');
			foreach ($aFirms as $key => $value) {
				if($nIDFirm == $key) {
					$oResponse->setFormElementChild('form1','nIDFirm',array("value"=>$key,"selected"=>"selected"),$value);
				} else {
					$oResponse->setFormElementChild('form1','nIDFirm',array("value"=>$key),$value);
				}
			}
			
			$oDBNomenclaturesServicesFirms = new DBNomenclaturesServicesFirms();
			$oDBNomenclaturesServicesFirms->getReport($nIDFirm,$oResponse);
			
			$oResponse->printResponse("Услуги фирми","nomenclatures_services_firms");
		}
	}

?>