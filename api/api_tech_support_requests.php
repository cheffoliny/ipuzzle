<?php
require_once("pdf/pdf_contract.php");
	class ApiTechSupportRequests {
		
		public function load( DBResponse $oResponse ) {
			$oFirms	= new DBFirms();
			$oDBCities = new DBCities();
			$oOffices	= new DBOffices();
			$aFirms = array();
			$aFirms	= $oFirms->getFirms();

			$nIDOffice = $_SESSION['userdata']['id_office'];
			$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
			
			
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0), 'Избери');
			
			foreach ( $aFirms as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
			}
			
			/* Първоначално зареждане по регион */
			$oResponse->setFormElement('form1', 'nIDFirm', array('value' => $nIDFirm), '');
			$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');
			foreach ( $aOffices as $key => $val ) {
				if ( $key == $nIDOffice ) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
				}
			}
			/* *** */
			
//			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
//			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');
			
			$aCities = $oDBCities -> getCitiesWithIDOffice();
			
			$oResponse->setFormElement('form1', 'nIDCity', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDCity', array("value"=>'0'), "Всички");
			foreach($aCities as $key => $value) {
				$oResponse->setFormElementChild('form1', 'nIDCity', array("value"=>$key), $value);
			}

			$oDBTechSupportRequestsFilters = new DBTechSupportRequestsFilters();
			
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$aFilters = array();
			$aFilters = $oDBTechSupportRequestsFilters->getFiltersByIDPerson($nIDPerson);
			
			$oResponse->setFormElement('form1','schemes');
			$oResponse->setFormElementChild('form1','schemes',array("value" => "0"),"---Изберете---");
			
			foreach ($aFilters as $key => $value) {
				if($value['is_default'] == '1') {
					$oResponse->setFormElementChild('form1','schemes',array("value" => $key,"selected" => "selected"),$value['name']);
				} else {
					$oResponse->setFormElementChild('form1','schemes',array("value" => $key),$value['name']);
				}
			}
			
			$oResponse->printResponse();
		}

		public function deleteFilter() {
			
			$nIDFilter = Params::get('schemes','0');
			
			$oDBTechSupportRequestsFilters = new DBTechSupportRequestsFilters();
			
			$oDBTechSupportRequestsFilters->delete($nIDFilter);
			
		}
		
		public function result( DBResponse $oResponse) {
			
			$nID = Params::get('id_contract','0');
			$sContractStatus = Params::get('sContractStatus','');
			$nIDCity 	= Params::get('nIDCity','0');
			$dFrom		= jsDateToTimestamp( Params::get("date_from", '') );
			$dTo		= jsDateToTimestamp( Params::get("date_to", '') );
			
			$sType = Params::get('type',0);
			
			
			//проверява за Задачи неизпълнени в период от 7 дена от датата на планиран старт и ги връща за планиране
			$oDBTechRequests = new DBTechRequests();
			$oDBTechRequests->resetOldRequests( $oResponse );
			
			
			if ( $sType == 'contracts' ) {
			
				$sApiAction = Params::get("api_action", "");
				
				if( $sApiAction == 'export_to_pdf') {
					$oPDF = new ContractPDF("L");
					$oPDF -> PrintReport($nID);
				} else {
			
					$aData = array();
					$aData['status'] = $sContractStatus;
					$aData['id_city'] = $nIDCity;
					$aData['date_from'] = $dFrom;
					$aData['date_to'] = $dTo;
					
					$oDBContracts = new DBContracts();
					$oDBContracts->getReport($oResponse,$aData);
					$oResponse->printResponse("Електронни договори","contracts");
				}
			} else {
				$aData	= array();
	
				$aData['id_firm']					= Params::get("nIDFirm", 0);
				$aData['id_office']					= Params::get("nIDOffice", 0);
				$aData['id_object']					= Params::get("nObject", 0);
				$aData['have_no_limit_card']		= Params::get("nNoLimitCard", 0);
				$aData['have_active_limit_card']	= Params::get("nActiveLC", 0);
				$aData['type']						= Params::get("sTypeR", '');
				$aData['startTime']					= jsDateToTimestamp( Params::get("date_from", '') );
				$aData['endTime']					= jsDateToTimestamp( Params::get("date_to", '') );
				$aData['nIDScheme']					= Params::get('schemes',0);
				
				
				$oTechSupportRequests	= new DBTechRequests();
				
				$oTechSupportRequests->getReport( $aData, $oResponse );
				
				$oResponse->printResponse("Задачи", "tech_requests");
			}
			
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
		
		public function loadOffices( DBResponse $oResponse ) {
			$nIDFirm = Params::get("nIDFirm", 0);
			
			$oOffices = new DBOffices();
			$aOffices = array();
			$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');	
			foreach ( $aOffices as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
			}
			$oResponse->printResponse();
		}
		
		function delete( DBResponse $oResponse ) {
			$chk = Params::get('chk', 0);
			$bla = array();
			$oTechRequests = new DBTechRequests();
			
			foreach( $chk as $k => $v ) {
				if ( !empty($v) ) {
					array_push($bla, $k);
				}
			}
			
			if ( !empty($bla) ) {
				$par = implode( ",", $bla );
				$oTechRequests->delRequests( $par );
			}
			
			$oResponse->printResponse();
		}

		function limit( DBResponse $oResponse ) {
			$chk = Params::get('chk', 0);
			$bla = array();
			$oTechRequests = new DBTechRequests();
			
			foreach( $chk as $k => $v ) {
				if ( !empty($v) ) {
					array_push($bla, $k);
				}
			}
			
			if ( !empty($bla) ) {
				$par = implode( ",", $bla );
				$oTechRequests->makeLimitCard( $par );
			}
			
			$oResponse->setAlert( sprintf("Изгенерирани бяха %u лимитни карти.", count( $bla )));

			$oResponse->printResponse();
		}
	}
?>