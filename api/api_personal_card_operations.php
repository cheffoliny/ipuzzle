<?php

	class ApiPersonalCardOperations {
		
		public function result(DBResponse $oResponse) {
			
			$nIDPerson = Params::get('id_person','0');
			$nIDLimitCard = Params::get('nIDLimitCard',0);
			
			$oDBTechLimitCard = new DBTechLimitCards();
			$oDBLimitCardPersons = new DBLimitCardPersons();
			$oDBTechSettings = new DBTechSettings();
			$oDBTechRequests = new DBTechRequests();
			$oDBLimitCardOperations = new DBLimitCardOperations();
			$oDBPersonnel = new DBPersonnel();
			$oDBObjects = new DBObjects();
			$oDBOffices = new DBOffices();
			
			$aLimitCard = $oDBTechLimitCard->getRecord($nIDLimitCard);
			
			//$nIDPerson = $_SESSION['userdata']['id_person'];
			
			$aPerson = $oDBPersonnel->getRecord($nIDPerson);
			$aOffice = $oDBOffices->getRecord($aPerson['id_office']);
			$nFactorOffice = $aOffice['factor_tech_support'];
			
			$nPersonFactor = $aPerson['tech_support_factor'];
			$nPersonPercent = $oDBLimitCardPersons->getPercent($nIDLimitCard,$nIDPerson);		
			
			//throw new Exception($nPersonFactor);
			
			$aTechSettings = $oDBTechSettings->getRecord(1);
			
			$nEarning = 0;
			if ( ($aLimitCard['type'] == 'create') || ($aLimitCard['type'] == 'arrange') ) {
				$aEarnings = $oDBLimitCardOperations -> getEarning($nIDLimitCard);
				$nEarning = $aEarnings['price1'];
				//throw new Exception("fds");				
			} else {
				switch ($aLimitCard['type']) {
					case 'destroy': $nEarning = $aTechSettings['tech_price_destroy'];break;
					//case 'arrange': $nEarning = $aTechSettings['tech_price_arrange'];break;
					case 'holdup': $nEarning = $aTechSettings['tech_price_holdup'];break;
				}				
			}
			
			//throw new Exception($nEarning);
			
			if( empty($aLimitCard['id_object'])) {
				if(empty($aLimitCard['id_request'])) {
					throw new Exception('Лимитната карта няма привързана Задача');
				} else {
					$aFactors = $oDBTechRequests -> getFactorTechSupport($aLimitCard['id_request']);
					//$nFactorOffice = $aFactors['factor_tech_support'];
				}
			} else {
				$aFactors = $oDBObjects -> getFactorTechSupport($aLimitCard['id_object']);
				//$nFactorOffice = $aFactors['factor_tech_support'];
			}
			//throw new Exception($nIDLimitCard);
			if( $aLimitCard['distance'] > 15 ) $nEarning += ($aLimitCard['distance'] - 15) * $aTechSettings['tech_price_km'] * $aFactors['factor_tech_distance'];
			
			$sEarning = "Наработка ";
			
			if( $nPersonFactor != 1 || $nPersonPercent != 100 || $nFactorOffice != 1) {
				$sEarning .= "(";
				
				if( $nPersonFactor != 1 ) {
					$nEarning *= $nPersonFactor;
					$aInfo[] = "личен: ".($nPersonFactor*100).'%';
				}
				if( $nPersonPercent != 100 ) {
					$nEarning *= $nPersonPercent/100;
					$aInfo[] = "карта: ".$nPersonPercent.'%';
				}
				if( $nFactorOffice != 1 ) {
					$nEarning *= $nFactorOffice;
					$aInfo[] = "регион: ".($nFactorOffice*100).'%';
				}
				
				
				$sEarning .= implode(', ',$aInfo);
				
				$sEarning .= ") ";
			}
			
			
			
			$nEarning = round($nEarning,2);
			$sEarning .= $nEarning." лв.";
			
			if( !empty($nEarning) ) {
				$oResponse -> setFormElement('form1','nEarning',array(),$nEarning);
				$oResponse -> setFormElement('form1','sEarning',array(),$sEarning);
			}
			
			if( $aLimitCard['type'] == 'create' || $aLimitCard['type'] == 'arrange') {
				if(!empty($aEarnings['price2']))
				$oResponse -> setFormElement('form1','sEarningLimitCard',array(),"Лимитна карта: ".$aEarnings['price2']."лв. / ".$aEarnings['price1']."лв." );
			}
			
			$aLimitCardOperations = $oDBLimitCardOperations -> getReport($aLimitCard,$oResponse);
			
			$oResponse->printResponse();
		}

		
		public function delete( DBResponse $oResponse ) {
			
			$nIDLimitCardOperation = Params::get('nIDLimitCardOperation','0');
			
			$oDBLimitCardOperation = new DBLimitCardOperations();
			
			$oDBLimitCardOperation -> delete($nIDLimitCardOperation);
			
			$oResponse->printResponse();
			
		}
		
		public function save( DBResponse $oResponse) {
			
			$aParams = Params::getAll();
			
			$aOperations =  Params::get('chk','');
			
			$oDBLimitCardOperation = new DBLimitCardOperations();
			
			foreach ($aOperations as $key => $value) {
				$aData = array();
				$aData['id'] = $key;
				$aData['is_done'] = $value;
				$oDBLimitCardOperation->update($aData);
			}
			
			APILog::Log(0,$aOperations);
			
			$oResponse->printResponse();
		}
		
		public function confirm( DBResponse $oResponse ) {
			
			$nIDLimitCardOperation = Params::get('nIDLimitCardOperation','0');
			
			$oDBLimitCardOperation = new DBLimitCardOperations();
			
			$aLimitCardOperation = $oDBLimitCardOperation -> getRecord($nIDLimitCardOperation);
			$aLimitCardOperation['is_done'] = 1;
			$oDBLimitCardOperation -> update($aLimitCardOperation);
			
			$oResponse->printResponse();	
		}
		
		public function unconfirm( DBResponse $oResponse ) {
			
			$nIDLimitCardOperation = Params::get('nIDLimitCardOperation','0');
			
			$oDBLimitCardOperation = new DBLimitCardOperations();
			
			$aLimitCardOperation = $oDBLimitCardOperation -> getRecord($nIDLimitCardOperation);
			$aLimitCardOperation['is_done'] = 0;
			$oDBLimitCardOperation -> update($aLimitCardOperation);
			
			$oResponse->printResponse();
			
		}
	}

?>