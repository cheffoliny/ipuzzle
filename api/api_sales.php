<?php 

	class ApiSales {
		
		public function result(DBResponse $oResponse) {
		
			$aParams = Params::getAll();
			$oDBObjectServices 	= new DBObjectServices();
			
			
			if ( $aParams['search_type'] == "by_client" ) {
				if ( empty($aParams['client_eik']) ) {
					throw new Exception("Няма въведен ЕИК на клиент по който да бъде направено търсене !!!");
				}
				
				$oDBObjectServices->getReportSales($oResponse, $aParams);
			} elseif ( $aParams['search_type'] == "by_object" ) {
				if ( empty($aParams['id_object']) ) {
					throw new Exception("Няма избран обект с помоща на подсказка !!!");
				}
				
				$oDBObjectServices->getReportSales($oResponse, $aParams);
			} elseif ( $aParams['search_type'] == "by_doc" ) {
				if ( empty($aParams['doc_num']) && empty($aParams['id_client']) ) {
					throw new Exception("Изберете номер на документ за търсене или изберете клиент с помоща на подсказка !!!");
				}
				
				$oDBSalesDocs = new DBSalesDocs();
				$oDBSalesDocs->getReportSales($oResponse, $aParams);
			}
			
			$oResponse->printResponse("Продажба", "sales");
		}
		
		public function init( DBResponse $oResponse ) {
			$oStatuses = new DBStatuses();
			$aStatuses = array();
			
			$oResponse->setFormElement( "form1", "nIDStatus", array(), NULL);
			$oResponse->setFormElementChild( "form1", "nIDStatus", array("value" => 0), "Всички");
			
			$aStatuses = $oStatuses->getStatuses();
			
			foreach ( $aStatuses as $key => $val ) {
				if ( $key == 1 ) {
					$oResponse->setFormElementChild( "form1", "nIDStatus", array("value" => $key, "selected" => "selected"), $val);
				} else {
					$oResponse->setFormElementChild( "form1", "nIDStatus", array("value" => $key), $val);
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function getPayment( DBResponse $oResponse ) {
			
			$oServices 	= new DBObjectServices();
			
			$nID 		= Params::get("id_object", 0);
			$sServices 	= "";
			$sSingles 	= "";
			
			$sServices 	= $oServices->getPaidServiceByObj( $nID );
			$sSingles 	= $oServices->getPaidSingleByObj( $nID );
			
			if ( ($sServices > 0) && ($sSingles > 0) ) {
				$sDate 	= $sServices < $sSingles ? date("m.Y", $sServices) : date("m.Y", $sSingles);
			} elseif ( ($sServices == 0) && ($sSingles == 0) ) {
				$sDate 	= "";
			} else {
				$sDate 	= $sServices > $sSingles ? date("m.Y", $sServices) : date("m.Y", $sSingles);
			}
	
			$oResponse->setFormElement( "form1", "min_paid", array(), $sDate);
			
			$oResponse->printResponse();
		}		
		
		public function faktura(DBResponse $oResponse) {	// ГЕНЕРИРАНЕ НА ФАКТУРА
			
			$aParams = Params::getAll();
			
			$nIDClient = isset($aParams['client_eik']) ? $aParams['client_eik'] : 0;
			
			$aCheckboxes = isset($aParams['chk']) ? $aParams['chk'] : array();
			
			foreach ($aCheckboxes as $key => $value) {
				if(empty($value)) {
					unset($aCheckboxes[$key]);
				}
			}
			unset($key);unset($value);
			
			if(!empty($aCheckboxes)) {
				
				$oDBSalesDocs 		= new DBSalesDocs();
				$oDBObjectServices 	= new DBObjectServices();
				$oDBObjectsSingles 	= new DBObjectsSingles();
				$oDBClientsObjects 	= new DBClientsObjects();
				
				$aServices = array();
				
				foreach ($aCheckboxes as $key => $value) {
					$aServices[] = $key;
				}
				unset($key);unset($value);
				
				$aJurNames = array();
				
				foreach ($aServices as $key => $value) {
					$aParts = explode(",",$value);
					
					if($aParts[1] == 'single') {
						$aJur = $oDBObjectsSingles->getJur($aParts[0]);
						$sJurName = $aJur['jur_name'];
					} else {
						$aJur = $oDBObjectServices->getJur($aParts[0]);
						$sJurName = $aJur['jur_name'];
					}
					$aJurNames[$sJurName] = 1;
				}
				
				unset($key);unset($value);
				
				if (count($aJurNames) > 1) {
					$aJurNames2 = array();
					foreach ($aJurNames as $key => $value ) {
						$aJurNames2[] = $key;
					}
					
					$sJurNames = implode(",",$aJurNames2);
					
					throw new Exception("Не може да издадете фактура с различни доставчици!!!\nЮридически лица: ".$sJurNames);
				}
				
				if ( empty($nIDClient) ) {
					
					list($nServiceID,$sServiceType) = explode(",", $aServices[0]);
					
					if($sServiceType == 'single') {						
						$aService = $oDBObjectsSingles->getRecord($nServiceID);
					} else {
						$aService = $oDBObjectServices->getRecord($nServiceID);
					}
					
					$nIDClient = $oDBClientsObjects->getIDClientByIDObject($aService['id_object']);
				}
				
				$nIDSaleDoc = $oDBSalesDocs->makeSaleDoc($nIDClient,$aServices,'faktura','proforma');
				$oResponse->setFormElement('form1','id_sale_doc',array(),$nIDSaleDoc);
				
			}
			
			$oResponse->printResponse();
		}
		
		public function faktura2(DBResponse $oResponse) {	// ГЕНЕРИРАНЕ НА ФАКТУРА Pavel
			
			$aParams 		= Params::getAll();
			$nIDClient 		= isset($aParams['client_eik']) ? $aParams['client_eik'] 	: 0;
			$aCheckboxes 	= isset($aParams['chk']) 		? $aParams['chk'] 			: array();
			
			foreach ( $aCheckboxes as $key => $value ) {
				if ( empty($value) ) {
					unset($aCheckboxes[$key]);
				}
			}
			
			unset($key); 
			unset($value);
			
			if ( !empty($aCheckboxes) ) {
				
				$oDBSalesDocs 		= new DBSalesDocs();
				$oDBObjectServices 	= new DBObjectServices();
				$oDBObjectsSingles 	= new DBObjectsSingles();
				$oDBClientsObjects 	= new DBClientsObjects();
				
				$aServices = array();
				
				foreach ( $aCheckboxes as $key => $value ) {
					$aServices[] = $key;
				}
				
				unset($key);
				unset($value);
				
				$aJurNames = array();
				
				foreach ( $aServices as $key => $value ) {
					$aParts = explode(",", $value);
					
					if ($aParts[1] == 'single') {
						$aJur 		= $oDBObjectsSingles->getJur($aParts[0]);
						$sJurName 	= $aJur['jur_name'];
					} else {
						$aJur 		= $oDBObjectServices->getJur($aParts[0]);
						$sJurName 	= $aJur['jur_name'];
					}
					
					// Prowerka za unikalnost na firmite - trqbva da e edna
					$aJurNames[$sJurName] = 1;
				}
				
				unset($key);
				unset($value);
				
				if ( count($aJurNames) > 1 ) {
					$aJurNames2 = array();
					
					foreach ( $aJurNames as $key => $value ) {
						$aJurNames2[] = $key;
					}
					
					unset($key);
					unset($value);
									
					$sJurNames = implode(",", $aJurNames2);
					
					throw new Exception("Не може да издадете фактура с различни доставчици!!!\nЮридически лица: ".$sJurNames);
				}
				
				if ( empty($nIDClient) ) {
					
					list( $nServiceID, $sServiceType ) = explode( ",", $aServices[0] );
					
					if ( $sServiceType == 'single' ) {						
						$aService = $oDBObjectsSingles->getRecord( $nServiceID );
					} else {
						$aService = $oDBObjectServices->getRecord( $nServiceID );
					}
					
					$nIDClient = $oDBClientsObjects->getIDClientByIDObject( $aService['id_object'] );
				}
				
				$nIDSaleDoc = $oDBSalesDocs->makeSaleDoc($nIDClient, $aServices, 'faktura', 'proforma');
				$oResponse->setFormElement('form1','id_sale_doc',array(),$nIDSaleDoc);
				
			}
			
			$oResponse->printResponse();
		}
				
		public function receipt(DBResponse $oResponse) { 	// ГЕНЕРИРАНЕ НА КВИТАНЦИЯ
			$aParams = Params::getAll();
			$simple  = Params::get("simple", 0);
			
			$nIDClient = isset($aParams['client_eik']) ? $aParams['client_eik'] : '';
			
			// pavel
			// Omazvacia na n-ta stepen
			if ( empty($nIDClient) ) {
				//wtf ?!! throw new Exception("Няма избран клиент");
				$oDBClientsObjects 	= new DBClientsObjects();
				
				$nIDObject 			= isset($aParams['id_object']) && is_numeric($aParams['id_object']) ? $aParams['id_object'] : 0;
				$nIDClient 			= $oDBClientsObjects->getIDClientByIDObject( $nIDObject );
				
				if ( empty($nIDClient) ) {
					throw new Exception("Няма избран клиент");
				}
			}
			
			$aCheckboxes = isset($aParams['chk']) ? $aParams['chk'] : '';
			
			foreach ($aCheckboxes as $key => $value) {
				if ( empty($value) ) {
					unset($aCheckboxes[$key]);
				}
			}
			unset($key);unset($value);
			
			if ( !empty($aCheckboxes) ) {
				$oDBSalesDocs 	= new DBSalesDocs();
				$oDBOrders 		= new DBOrders();
				$oDBSystem 		= new DBSystem();
				
				$aServices = array();
				
				foreach ($aCheckboxes as $key => $value) {
					$aServices[] = $key;
				}
				unset($key);unset($value);
				
				$type = !empty($simple) ? "kvitanciq1" : "kvitanciq";
				//throw new Exception($type);
				$nIDDocs = $oDBSalesDocs->makeDocs($nIDClient, $aServices, $type, 'final');
				
				$sIDDocs = implode(",", $nIDDocs);
				$oResponse->setFormElement('form1', 'sIDDocs', array(), $sIDDocs);
				
			}
			
			$oResponse->printResponse();
		}
	}

?>