<?php

	class ApiSetNomenclatureEarning {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			
			if(!empty($nID)) {
				
				$oDBNomenclaturesEarnings = new DBNomenclaturesEarnings();
				
				$aNomenclatureEarning = $oDBNomenclaturesEarnings->getRecord($nID); 
				
				$oResponse->setFormElement('form1','sCode',array(),$aNomenclatureEarning['code']);
				$oResponse->setFormElement('form1','sName',array(),$aNomenclatureEarning['name']);
				if(!empty($aNomenclatureEarning['is_system'])) {
					$oResponse->setFormElement('form1','is_system',array("checked" => "checked"));
				}
				
				$oResponse->printResponse();
			}
		}
		
		public function save() {
			
			$nID = Params::get('nID',0);
			$sCode = Params::get('sCode','');
			$sName = Params::get('sName','');
			$nIsSystem = Params::get('is_system',0);
			
			if(empty($sCode)) {
				throw new Exception("Въведете код на номенклатурата приход");
			}
			
			if(empty($sName)) {
				throw new Exception("Въведете име на номенклатурата приход");
			}
			
			$oDBNomenclaturesEarnings = new DBNomenclaturesEarnings();
			
			$aData = array();
			
			$aData['id'] = $nID;
			$aData['code'] = $sCode;
			$aData['name'] = $sName;
			$aData['is_system'] = $nIsSystem;
			
			$oDBNomenclaturesEarnings->update($aData);
			
		}
	}
?>