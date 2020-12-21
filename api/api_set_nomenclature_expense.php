<?php

	class ApiSetNomenclatureExpense {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			
			if ( !empty($nID) ) {
				
				$oDBNomenclaturesExpenses 	= new DBNomenclaturesExpenses();
				$aNomenclatureExpense 		= $oDBNomenclaturesExpenses->getRecord($nID); 
				
				$oResponse->setFormElement('form1', 'sCode',array(),$aNomenclatureExpense['code']);
				$oResponse->setFormElement('form1', 'sName',array(),$aNomenclatureExpense['name']);

				if ( !empty($aNomenclatureExpense['for_salary']) ) {
					$oResponse->setFormElement('form1', 'for_salary',array("checked" => 'checked'));
				}
				
				if ( !empty($aNomenclatureExpense['for_gsm']) ) {
					$oResponse->setFormElement('form1', 'for_gsm',array("checked" => 'checked'));
				}
				
				if ( !empty($aNomenclatureExpense['for_dds']) ) {
					$oResponse->setFormElement('form1', 'for_dds',array("checked" => 'checked'));
				}
				
				if ( !empty($aNomenclatureExpense['for_transfer']) ) {
					$oResponse->setFormElement('form1', 'for_trans',array("checked" => 'checked'));
				}				
				
				$oResponse->printResponse();
			}
		}
		
		public function save() {
			
			$nID 			= Params::get('nID',		0);
			$sCode 			= Params::get('sCode',		0);
			$sName 			= Params::get('sName',		0);
			$nForSalary 	= Params::get('for_salary',	0);
			$nForGSM 		= Params::get('for_gsm',	0);
			$nForDDS 		= Params::get('for_dds',	0);
			$nForTransfer	= Params::get('for_trans', 	0);
			
			if ( empty($sCode) ) {
				throw new Exception("Въведете код на номенклатурата разход");
			}
			
			if( empty($sName) ) {
				throw new Exception("Въведете име на номенклатурата разход");
			}
			
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			
			if ( !empty($nForSalary) ) {
				$oDBNomenclaturesExpenses->resetField('for_salary');
			}
			
			if ( !empty($nForDDS) ) {
				$oDBNomenclaturesExpenses->resetField('for_dds');
			}
			
			if ( !empty($nForGSM) ) {
				$oDBNomenclaturesExpenses->resetField('for_gsm');
			}
			
			$aData = array();
			
			$aData['id'] 			= $nID;
			$aData['code'] 			= $sCode;
			$aData['name'] 			= $sName;
			$aData['for_salary'] 	= $nForSalary;
			$aData['for_gsm'] 		= $nForGSM;
			$aData['for_dds'] 		= $nForDDS;
			$aData['for_transfer'] 	= $nForTransfer;		
			
			$oDBNomenclaturesExpenses->update($aData);
			
		}
	}
?>