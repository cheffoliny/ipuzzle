<?php
require_once("pdf/pdf_contract.php");
	
	class ApiContracts
	{
		public function load( DBResponse $oResponse ) {
			
			$oDBCities = new DBCities();
				
			$oResponse->setFormElement('form1', 'sContractStatus', array(), '');
			$oResponse->setFormElementChild('form1', 'sContractStatus', array("value"=>'0'), "Всички");
			$oResponse->setFormElementChild('form1', 'sContractStatus', array_merge(array("value"=>'entered'),array("selected"=>"selected")), "Чакащи");
			$oResponse->setFormElementChild('form1', 'sContractStatus', array("value"=>'validated'), "Валидирани");
			$oResponse->setFormElementChild('form1', 'sContractStatus', array("value"=>'ignored'), "Отказани");

			$aCities = $oDBCities -> getCitiesWithIDOffice();
			
			
			$oResponse->setFormElement('form1', 'nIDCity', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDCity', array("value"=>'0'), "Всички");
			foreach($aCities as $key => $value) {
				$oResponse->setFormElementChild('form1', 'nIDCity', array("value"=>$key), $value);
			}

			$oResponse->printResponse();
			
		}
		
		public function ignoreContract() {
			$nIDContract = Params::get('id_contract','');

			if( !empty($nIDContract)) {
				$oDBContracts = new DBContracts();
				$oDBTechRequest = new DBTechRequests();
			
				$aData = array();
				$aData['id'] = $nIDContract;
				$aData['contract_status'] = 'ignored';
				$oDBContracts->update($aData);
				
				$oDBTechRequest->delByIDContract($nIDContract);
			}
		}
		
		public function result( DBResponse $oResponse) {
			
			$nID = Params::get('id_contract','0');
			$nPages = Params::get('nPages','0');
			$sContractStatus = Params::get('sContractStatus','');
			$nIDCity 	= Params::get('nIDCity','0');
			$dFrom		= jsDateToTimestamp( Params::get("date_from", '') );
			$dTo		= jsDateToTimestamp( Params::get("date_to", '') );
			
			$sApiAction = Params::get("api_action", "");
			
			

			if( $sApiAction == 'export_to_pdf') {
				$oPDF = new ContractPDF("P");
				$oPDF -> PrintReport($nID,$nPages);
			} else {
		
				$aData = array();
				$aData['status'] = $sContractStatus;
				$aData['id_city'] = $nIDCity;
				$aData['date_from'] = $dFrom;
				$aData['date_to'] = $dTo;
				
				$oDBContracts = new DBContracts();
				$oDBContracts->getReport($oResponse,$aData);
				$oResponse->printResponse();
			}
			
		}
	}
?>