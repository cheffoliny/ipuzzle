<?php

	class ApiSetLimitCardOperation {
		
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			$nIDLimitCard = Params::get('id_limit_card','0');
			
			
			if(!empty($nID)) {
				
				$oDBLimitCardOperations = new DBLimitCardOperations();
				
				$aLimitCardOperation = $oDBLimitCardOperations->getLimitCardOperation($nID);
			
				$oResponse->setFormElement('form1','sName',array(),$aLimitCardOperation['name']);
				$oResponse->setFormElement('form1','nQuantity',array(),$aLimitCardOperation['quantity']);
			
			} else {
				
				$oDBTechLimitCards = new DBTechLimitCards();
				$oDBTechRequests = new DBTechRequests();
				$oDBTechOperations = new DBTechOperations();
				
				$aLimitCard = $oDBTechLimitCards->getRecord($nIDLimitCard);
				$aRequests = $oDBTechRequests->getRecord($aLimitCard['id_request']);
				
				if($aRequests['tech_type'] != 'contract' and $aRequests['type'] == 'create') {
					$aTechOperations = $oDBTechOperations -> getOperationsForArrange();
				} else {
					$aTechOperations = $oDBTechOperations -> getOperations();
				}
				
				$oResponse->setFormElement('form1','nOperations',array());
				$oResponse->setFormElementChild('form1','nOperations',array('value' => '0'),'---Избери---');
				foreach ( $aTechOperations as $key => $value ) {
					$oResponse->setFormElementChild('form1','nOperations',array('value' => "{$key}"),$value);
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			$nID = Params::get('nID','0');
			$nIDOperation = Params::get('id_operation','');
			$nIDLimitCard = Params::get('id_limit_card','0');
			$nQuantity = Params::get('nQuantity','0');
			
			
			if(empty($nQuantity)) {
				throw new Exception('Въведете количество');
			}
			
			$oDBLimitCardOperations = new DBLimitCardOperations();

			if(!empty($nID)) {
				
				$oDBTechRequests = new DBTechRequests();
				$oDBTechOperations = new DBTechOperations();
				$oDBTechLimitCards = new DBTechLimitCards();
				
				$aLimitCard = $oDBTechLimitCards->getRecord($nIDLimitCard);
				$aRequest = $oDBTechRequests->getRecord($aLimitCard['id_request']);
				
				$aLimitCardOperation = $oDBLimitCardOperations->getRecord($nID);
				$aTechOperation = $oDBTechOperations->getRecord($aLimitCardOperation['id_operation']);
							
				if($aLimitCard['type'] == 'create' && $aRequest['tech_type'] == 'contract') {
					
					$oDBContracts = new DBContracts();
					$oTechOperationsNomenclatures = new DBTechOperationsNomenclatures();
					$oDBPPPElements = new DBPPPElements();
								
					
					$aContract = $oDBContracts->getRecord($aRequest['id_contract']);
					$aNomenclatures = $oTechOperationsNomenclatures -> getNomenclaturesByIdOperation($aLimitCardOperation['id_operation']);
					$sNomenclatures = implode(',',$aNomenclatures);
					
					if(!empty($sNomenclatures)) {
						$nCountNomenclatures = $oDBPPPElements->countNomenclatures($nIDLimitCard,$sNomenclatures);
					} else {
						$nCountNomenclatures = 0;
					}
					
					
					if($aTechOperation['cable_operation']) {
						
						$aLimitCardOperations = $oDBLimitCardOperations->getLimitCardOperations($aLimitCardOperation['id_limit_card']);
						$nCountOperation = count($aLimitCardOperations);
						$nDetectors = $aContract['count_detectors'];
						
						if( $nQuantity > ($nCountOperation + $nDetectors - 2)) {
							throw new Exception('Неможе да въведете количество по-голямо планираното');
						}
						
					} elseif(!empty($nCountNomenclatures)) {
						if( $nQuantity > $nCountNomenclatures ) {
							throw new Exception('Неможе да въведете количество по-голямо от планираното');
						}
					} else {
						if( $nQuantity > 1 ) {
							throw new Exception('Неможе да въведете количество по-голямо от планираното');
						}
					}
					
				}
				
				$aLimitCardOperation['quantity'] = $nQuantity;
				$oDBLimitCardOperations -> update($aLimitCardOperation); 
				
			} else {
			
				$nIDOperation = Params::get('nOperations','');
				
				if(empty($nIDOperation)) {
					throw new Exception('Изберете операция');
				}
				
				$aLimitCardOperation = array();
				$aLimitCardOperation['id_operation'] = $nIDOperation;
				$aLimitCardOperation['id_limit_card'] = $nIDLimitCard;
				$aLimitCardOperation['quantity'] = $nQuantity;
				
				$oDBLimitCardOperations -> update($aLimitCardOperation); 	
			}
			
		}
	}

?>