<?php
	require_once("pdf/pdf_contract.php");

	class ApiPersonalCardLimitCard {
		
		public function load( DBResponse $oResponse) {
			
			$nIDLimitCard = Params::get('id_limit_card','0');
			
			//throw new Exception($nIDLimitCard);
			
			if( !empty($nIDLimitCard)) {
				
				$oDBTechLimitCards = new DBTechLimitCards();
				$oDBTechRequests = new DBTechRequests();		
					
				$nIDObject = $oDBTechLimitCards->getIDObject($nIDLimitCard);
				
				if( !empty($nIDObject ) ) {
					$aLimitCard = $oDBTechLimitCards->getInfoForPersonCard($nIDLimitCard);
		
				} else {
					$aLimitCard = $oDBTechLimitCards->getInfoForPersonCard2($nIDLimitCard);	
				}
				
				
				$aRequest = $oDBTechRequests -> getInfoForPersonCard($aLimitCard['id_request']);
		
				$nIDContract = !empty($aRequest['id_contract']) ? $aRequest['id_contract'] : 0;
				
				//print($nIDContract);
				
				if(!empty($nIDContract)) {
					$oDBContracts = new DBContracts();
					$aContract = $oDBContracts->getInfoForPersonCard($nIDContract);
					
					//Информация по електронния договор
					
				} else {
					
					//Информация по Задачата

				}
				
				$sDistance = '';
				if(!empty($aLimitCard['distance']))$sDistance = $aLimitCard['distance']." км.";
				
				$oResponse->setFormElement('form1','sObjName',array(),$aLimitCard['obj_name']."  ".$sDistance);
				$oResponse->setFormElement('form1','sObjAddress',array(),$aLimitCard['obj_address']);
				$oResponse->setFormElement('form1','sPhone',array(),$aLimitCard['phone']);
				$oResponse->setFormElement('form1','sMOL',array(),$aLimitCard['face_name']);
				
			}
			
			$oResponse->printResponse();
		}
		
		public function realStart( DBResponse $oResponse) {
			global $db_sod, $db_finance;
			
			$nIDLimitCard = Params::get('id_limit_card','0');
			
			$oDBLimitCards = new DBTechLimitCards();
			$oDBObjects = new DBObjects();
			$oDBObjects2 = new DBObjects2();
			
			$db_sod->StartTrans();
			$db_finance->StartTrans();
			
			try {
				$aLimitCard = $oDBLimitCards -> getRecord($nIDLimitCard);
				$oDBLimitCards->setRealStart($nIDLimitCard);
				$sRealStart = $oDBLimitCards->getRealStart($nIDLimitCard);
	
				if (!empty($aLimitCard['id_object'])) {
					$aObject = $oDBObjects->getRecord($aLimitCard['id_object']);
					
					if ( $aObject['service_status'] != 1 ) {
						$aObject['service_status'] = 1;
						$oDBObjects->update($aObject);
					}
					
					if(!empty($aObject['id_oldobj'])) {
						$oDBObjects2->setServiceStatus($aObject['id_oldobj']);
						$oDBObjects2->setServiceStatus($aObject['id_oldobj']);
					}
				} else {
					$oDBTechRequests = new DBTechRequests();
					$oDBContracts 	 = new DBContracts();
					$aRequest 		 = $oDBTechRequests->getRecord($aLimitCard['id_request']);
					
					if( $aRequest['tech_type'] == 'contract') {   
					
						if ( !empty($aRequest['id_contract']) && is_numeric($aRequest['id_contract']) ) {
							$arr = array();
							$arr['id'] = $aRequest['id_contract'];
							$arr['contract_status'] = 'validated';
							
							$oDBContracts->update( $arr );
						}
					}
					
				}
				
				$db_sod->CompleteTrans();
				$db_finance->CompleteTrans();				
				
			} catch (Exception $e) {
				APILog::Log(0, $e->getMessage());
				$oResponse->setAlert($e->getMessage());
				
				$db_sod->FailTrans();
				$db_finance->FailTrans();					
			}
			
			$oResponse->setFormElement('form1','real_start',array(),$sRealStart);			
			$oResponse->printResponse();
			
		}
		
		public function realEnd( DBResponse $oResponse) {
			
			$nIDLimitCard = Params::get('id_limit_card','0');
			$nIDObject = Params::get('id_object','0');
			$nEarning = Params::get('earning','0');
			$nIDPerson = Params::get('id_person','0');
			
			$oDBLimitCards = new DBTechLimitCards();
			$oDBTechRequests = new DBTechRequests();
			$oDBSalary = new DBSalary();
			$oDBSalaryEarnings = new DBSalaryEarning();
			$oDBPPP = new DBPPP();
			$oDBObjects = new DBObjects();
			$oDBObjects2 = new DBObjects2();
			$oDBPersonnel = new DBPersonnel();
			
			$aLimitCard = $oDBLimitCards -> getRecord($nIDLimitCard);
			$aRequest = $oDBTechRequests -> getRecord($aLimitCard['id_request']);
			$aPersonnel = $oDBPersonnel -> getRecord($nIDPerson);
			
			if($aLimitCard['status'] == 'closed') {
				throw new Exception("Лимитната карта е затворена");
			}
			
			
			if(!empty($aLimitCard['id_object'])) {
				$aObject = $oDBObjects -> getRecord($aLimitCard['id_object']);
				if($aObject['service_status'] != 0) {
					$aObject['service_status'] = 0;
					$oDBObjects -> update($aObject);
				}
				

				
//				if(!empty($aObject['id_oldobj'])) {
//					$oDBObjects2->closeServiceStatus($aObject['id_oldobj']);
//					
//					if ( ($aRequest['type'] == "create" || $aRequest['type'] == "arrange") && $aRequest['tech_type'] != "contract" ) {	//ако изграждането не е от ел. договор се вкарва еднократно задължение към обкета в старата база
//						
//						$oDBLimitCardOperations = new DBLimitCardOperations();
//						$oDBOffices = new DBOffices();
//						$oDBSingles = new DBSingles();
//						$nQuantity = $oDBLimitCardOperations->countQuantity($nIDLimitCard);
//						$aLimitCardPrice = $oDBLimitCardOperations->getEarning($nIDLimitCard);
//						
//						$aOffice = $oDBOffices->getRecord($aObject['id_office']);
//						$nSingle = round(($aOffice['factor_object_single_from_arrange'] * $aLimitCardPrice['price1']),2);
//						
//						if(!empty($nSingle)) {
//							$sInfo = "АРАНЖИРОВКА КОМПОНЕНТИ СОТ ".$nQuantity." бр. / ".date('d-m-Y')." г.";
//						
//							$aSin = array();
//							$aSin['id_obj'] = $aObject['id_oldobj'];
//							$aSin['data'] = date('Y-m-d');
//							$aSin['info'] = iconv("utf-8","cp1251",trim($sInfo));
//							$aSin['currency'] = "BGL";
//							$aSin['sum'] = $nSingle;
//							$oDBSingles->update($aSin);
//						}			
//					}
//
//				}
				
				
				if ( !empty($aObject['id_oldobj']) ) {
					$oDBObjects2->closeServiceStatus($aObject['id_oldobj']);
				}
					
				if ( ($aRequest['type'] == "create" || $aRequest['type'] == "arrange") && $aRequest['tech_type'] != "contract" ) {	//ако изграждането не е от ел. договор се вкарва еднократно задължение към обкета в старата база
						
					$oDBLimitCardOperations = new DBLimitCardOperations();
					$oDBOffices 			= new DBOffices();
					$oDBSingles 			= new DBObjectsSingles();
					$nQuantity 				= $oDBLimitCardOperations->countQuantity($nIDLimitCard);
					$aLimitCardPrice 		= $oDBLimitCardOperations->getEarning($nIDLimitCard);
						
					$aOffice = $oDBOffices->getRecord($aObject['id_office']);
					$nSingle = round(($aOffice['factor_object_single_from_arrange'] * $aLimitCardPrice['price1']), 2);
						
					if ( !empty($nSingle) ) {
						$sInfo = "АРАНЖИРОВКА КОМПОНЕНТИ СОТ ".$nQuantity." бр. / ".date('d-m-Y')." г.";
						
						$aSin = array();
						$aSin['id_object'] 		= $aObject['id'];
						$aSin['id_office'] 		= $aObject['id_office'];
						$aSin['id_service'] 	= 26;
						$aSin['id_schet'] 		= 0;
						$aSin['service_name'] 	= trim($sInfo);
						$aSin['quantity'] 		= 1;
						$aSin['single_price'] 	= $nSingle * 1.2;
						$aSin['total_sum'] 		= $nSingle * 1.2;
						$aSin['start_date'] 	= date('Y-m-d');
						$aSin['paid_date'] 		= "0000-00-00";
						$aSin['id_sale_doc'] 	= 0;
						$aSin['to_arc']		 	= 0;
						
						$oDBSingles->update($aSin);
					}			
				}
			
				
				if($aLimitCard['type'] == 'destroy') {
					$aObject = $oDBObjects -> getRecord($aLimitCard['id_object']);
					$aObject['id_status'] = '4';
					$oDBObjects -> update($aObject);
					
					if(!empty($aObject['id_oldobj'])) {
						$oDBObjects2->makeNotActive($aObject['id_oldobj']);						
					}
				}
			}
			
			$oDBLimitCards->setRealEnd($nIDLimitCard);
			$sRealEnd = $oDBLimitCards->getRealEnd($nIDLimitCard);
			
			$oResponse->setFormElement('form1','real_end',array(),$sRealEnd);
			
			$nUnconfirmedPPPs = $oDBPPP->countUnconfirmedPPPs($nIDLimitCard);
			
			
			
			$oResponse->setFormElement('form1','unconfirmed_ppps',array(),$nUnconfirmedPPPs);
			
			//if( empty($nUnconfirmedPPPs) ) {
			
				$oDBLimitCards->closedStatus($nIDLimitCard);
				
				if( !empty ($nEarning )) {
					
					$oDBLimitCards->setSalaries($nIDLimitCard);
					
				}
			
				
			//}
			
			$oResponse->printResponse();
		}

		
		public function operationsDone( DBResponse $oResponse ) {
			
			$nIDLimitCard = Params::get('id_limit_card','0');
			
			$oDBLimitCardOperations = new DBLimitCardOperations();
			
			$nNotDoneOperations = $oDBLimitCardOperations->countNotDone($nIDLimitCard);
			$oResponse->setFormElement('form1','notDoneOperations',array(),$nNotDoneOperations);
			
			$oResponse->printResponse();
		}
		
		public function result(  DBResponse $oResponse) {
			
			$nIDContract = Params::get('id_contract');
			
			$sApiAction = Params::get("api_action", "");
			
			if( $sApiAction == 'export_to_pdf') {
				$oPDF = new ContractPDF("P");
				$oPDF -> PrintReport($nIDContract);
			}
			$oResponse->printResponse();
		}
	}

?>