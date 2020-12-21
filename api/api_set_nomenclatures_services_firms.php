<?php

	class ApiSetNomenclaturesServicesFirms {
		
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
		
			$oDBNomenclaturesServices = new DBNomenclaturesServices();	
			$oDBNomenclaturesServicesFirms = new DBNomenclaturesServicesFirms();
		
			$aNomenclaturesServices =  $oDBNomenclaturesServices->getIt( $nIDFirm );
			$aNomenclaturesServicesFirms = $oDBNomenclaturesServicesFirms->getItByIDFirm($nIDFirm);
			
			$oResponse->setFormElement('form1','all_services',array());
			$oResponse->setFormElement('form1','account_services',array());
			foreach ($aNomenclaturesServices as $value) {
				if(in_array($value['id'],$aNomenclaturesServicesFirms)) {
					$oResponse->setFormElementChild('form1','account_services',array("value" => $value['id']),sprintf("[%s] %s",$value['code'],$value['name']));
				} else {
					$oResponse->setFormElementChild('form1','all_services',array("value" => $value['id']),sprintf("[%s] %s",$value['code'],$value['name']));
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			
			$nIDFirm = Params::get('nIDFirm',0);
			
			$aAccountServices = Params::get('account_services',array());
			
			$oDBNomenclaturesServicesFirms = new DBNomenclaturesServicesFirms();
			
			$oDBNomenclaturesServicesFirms->delByIDFirm($nIDFirm);
			
			if(!empty($aAccountServices)) {
				foreach ($aAccountServices as $value) {
					$aData = array();
					$aData['id_nomenclature_service'] = $value;
					$aData['id_firm'] = $nIDFirm; 
					
					$oDBNomenclaturesServicesFirms->update($aData);
				}
			}
			
		}
	}

?>