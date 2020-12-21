<?php

	class ApiSetExportToEmail {
		
		public function load(DBResponse $oResponse) {
			$nID 		= Params::get("nID", 0);
			$aExport	= array();
			
			if ( !empty($nID) ) {
				$oExport = new DBExportToEmail();

				$aExport = $oExport->getRecord($nID); 
				
				$oResponse->setFormElement("form1", "sName", array(), isset($aExport['email']) ? $aExport['email'] : "");
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			$nID 	= Params::get("nID", 0);
			$sName 	= Params::get("sName", "");
			$aData 	= array();
			
			$oExport 	= new DBExportToEmail();
			$oValidate	= new Validate();

			if ( empty($sName) ) {
				throw new Exception("Въведете имейл за регистрация!");
			}
			
			$oValidate->variable = $sName;
			$oValidate->checkEMAIL();
			
			if ( $oValidate->result ) {
				$aData['id'] 	= $nID;
				$aData['email'] = $sName;
	
				$oExport->update($aData);
			} else {
				throw new Exception("Въведете кректен имейл!!!");
			}
		}
	}
?>