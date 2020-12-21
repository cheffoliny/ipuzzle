<?php
	class ApiSetSetupObjectSingles {
		
		public function result( DBResponse $oResponse ) {
			$nID 			 = Params::get("nID", 0);
			$nIDRegion		 = Params::get("nIDRegion", 0);
			$nIDFirm		 = Params::get("nIDFirm", 0);
			$nIDObject		 = Params::get('nIDObject', 0);	
						
			$aObjectServices = array();
			$aPrices 		 = array();
			$aFirms 		 = array();
			$oObjectServices = new DBObjectServices();
			$oOffices		 = new DBOffices();
			$oFirms			 = new DBFirms();			
			
			//$aObjectServices = $oObjectServices->getObjectServices(0);

			$oResponse->setFormElement('form1',		 'nIDFirm', array(), '');	
			$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0, 'id' => ''), 'Изберете фирма');
			
			$oResponse->setFormElement('form1',		 'nIDRegion', array(), '');	
			$oResponse->setFormElementChild('form1', 'nIDRegion', array('value' => 0, 'id' => ''), 'Изберете офис');

			$oResponse->setFormElement('form1',		 'nServices', array(), '');	
			$oResponse->setFormElementChild('form1', 'nServices', array('value' => 0, 'id' => ''), 'Изберете услуга');

			$this->getFirms();
			
			if( !empty( $nID ) ) {
				$aPrices = $oObjectServices->getSingleByID($nID);
				//APILog::Log(0, $aPrices);
				
				if ( empty($nIDFirm) ) {
					$nIDRegion = isset($aPrices['office']) ? $aPrices['office'] : 0; 
					
					if ( empty($nIDRegion) ) {
						$nIDRegion = $oOffices->getOfficesIDByObject($nIDObject); // Взимаме данните за офис по реакция
					}	
					
					$nIDFirm = $oOffices->getFirmByIDOffice( $nIDRegion );				
				} 
								
				$nIDService	= isset($aPrices['service']) ? $aPrices['service'] : 0;
				$name	 	= isset($aPrices['service_name']) ? $aPrices['service_name'] : '';
				$sprice 	= isset($aPrices['single_price']) ? $aPrices['single_price'] : 0;
				$quantity 	= isset($aPrices['quantity']) && !empty($aPrices['quantity']) ? $aPrices['quantity'] : 1;
				$tsum 		= isset($aPrices['total_sum']) ? $aPrices['total_sum'] : 0;
				//$paid 		= isset($aPrices['paid_date']) && $aPrices['paid_date'] != '00.0000' ? $aPrices['paid_date'] : "";
				$paid 		= isset($aPrices['start_date']) && $aPrices['start_date'] != '00.00.0000' ? $aPrices['start_date'] : "";
				$id_doc		= isset($aPrices['doc_num']) ? $aPrices['doc_num'] : 0;
				
				$doc_num 	= $oObjectServices->getDocNumById($id_doc);
				
				//$oResponse->setFormElement('form1',	'nServices', array(), $service);
				$oResponse->setFormElement('form1',	'sName', array(), $name);
				$oResponse->setFormElement('form1',	'nPrice', array(), $sprice);
				$oResponse->setFormElement('form1',	'nQuantity', array(), $quantity);
				$oResponse->setFormElement('form1',	'nSum', array(), $tsum);
				$oResponse->setFormElement('form1',	'sPaid', array(), $paid);
				$oResponse->setFormElement('form1',	'nDocNum', array(), $doc_num);	
				
//				foreach ( $aObjectServices as $key => $val ) {
//					$sID = $val['name']."@@@".$val['name_edit']."@@@".$val['quantity_edit']."@@@".$val['price_edit'];
//				
//					if ( $key == $nIDService ) {
//						$oResponse->setFormElementChild('form1', 'nServices', array('value' => $key, 'id' => $sID, 'selected' => 'selected'), $val['name']);
//					} else {				
//						$oResponse->setFormElementChild('form1', 'nServices', array('value' => $key, 'id' => $sID), $val['name']);
//					}
//				}
								
			} else {
				
				if ( empty($nIDFirm) ) {
					$nIDRegion 	= $oOffices->getOfficesIDByObject($nIDObject); // Взимаме данните за офис по реакция
					$nIDFirm 	= $oOffices->getFirmByIDOffice( $nIDRegion );				
				} 
								
				foreach ( $aObjectServices as $key => $val ) {
					$sID = $val['name']."@@@".$val['name_edit']."@@@".$val['quantity_edit']."@@@".$val['price_edit'];
					$oResponse->setFormElementChild('form1', 'nServices', array('value' => $key, 'id' => $sID), $val['name']);
				}				
				
				$oResponse->setFormElement('form1',	'nQuantity', array(), 1);
				$oResponse->setFormElement('form1',	'sPaid', array(), date('d.m.Y'));	
			}
	
			$aFirms	= $oFirms->getFirms();
			
			foreach ( $aFirms as $key => $val ) {
				if ( $nIDFirm == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
				} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
			}

			unset($key); unset($val);
			
			if ( !empty($nIDFirm) ) {
				$aOffices 			= $oOffices->getFirmOfficesAssoc( $nIDFirm );
				$aObjectServices 	= $oObjectServices->getObjectServices( $nIDFirm, 0 );
				
				foreach ( $aOffices as $key => $val ) {
					if ( $nIDRegion == $key ) {
						$oResponse->setFormElementChild('form1', 'nIDRegion', array('value' => $key, 'selected' => 'selected'), $val['name']);
					} else $oResponse->setFormElementChild('form1', 'nIDRegion', array('value' => $key), $val['name']);
				}	
			
				foreach ( $aObjectServices as $key => $val ) {
					$sID = $val['name']."@@@".$val['name_edit']."@@@".$val['quantity_edit']."@@@".$val['price_edit'];
				
					if ( $key == $nIDService ) {
						$oResponse->setFormElementChild('form1', 'nServices', array('value' => $key, 'id' => $sID, 'selected' => 'selected'), $val['name']);
					} else {				
						$oResponse->setFormElementChild('form1', 'nServices', array('value' => $key, 'id' => $sID), $val['name']);
					}
				}				
			
			}				
						
			$oResponse->printResponse();
		}
			
		public function getFirms() {
				
			$nIDObject	= Params::get('nIDObject', 0);	
			
			$oFirms		= new DBFirms();
			$oOffices	= new DBOffices();
			
			if ( empty($nIDFirm) ) {
				$nIDOffice = $oOffices->getOfficesIDByObject($nIDObject); // Взимаме данните за офис по реакция
				
				$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
				//APILog::Log(0, $nIDFirm);
			}				
		}		
		
		public function save( DBResponse $oResponse ) {
			$nID		= Params::get('nID', 0);
			$nIDObject	= Params::get('nIDObject', 0);
			$service 	= Params::get('nServices', 0);
			$nIDRegion	= Params::get('nIDRegion', 0);
			$name	 	= Params::get('sName', '');
			$sprice 	= Params::get('nPrice', 0);
			$quantity 	= Params::get('nQuantity', 0);
			$tsum 		= Params::get('nSum', 0);
			$paid 		= Params::get('sPaid', '');
			
			$now = time();
			
			if ( empty($service) ) {
				throw new Exception("Въведете услуга!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($nIDRegion) ) {
				throw new Exception("Въведете фирма/регион!!", DBAPI_ERR_INVALID_PARAM);
			}	
						
			if ( empty($sprice) ) {
				throw new Exception("Въведете единична цена!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($quantity) ) {
				throw new Exception("Въведете количество!", DBAPI_ERR_INVALID_PARAM);
			}
							
			if ( empty($paid) || $paid == '00.00.0000' ) {
				throw new Exception("Въведете дата за плащането!!", DBAPI_ERR_INVALID_PARAM);
			}
						
			$oObjectServices = new DBObjectServices();
			
			if ( !empty($nID) ) {
				$aPrices 	= $oObjectServices->getSingleByID($nID);
				$id_doc		= isset($aPrices['doc_num']) ? $aPrices['doc_num'] : 0;
				$doc_num 	= $oObjectServices->getDocNumById($id_doc);
			} else {
				$doc_num = 0;
			}

			$start_date = jsDateToTimestamp( $paid );
			
			if ( !empty($doc_num) ) {
				throw new Exception("Записа е привързан за платежен документ!!!", DBAPI_ERR_INVALID_PARAM);
			} else {
				$aData = array();
				$aData['id'] 			= $nID;
				$aData['id_object'] 	= $nIDObject;
				$aData['id_office'] 	= $nIDRegion;
				$aData['id_service'] 	= $service;
				$aData['service_name'] 	= $name;
				$aData['single_price'] 	= $sprice;
				$aData['quantity'] 		= $quantity;
				$aData['total_sum'] 	= $tsum;
				$aData['start_date'] 	= $start_date;
				$aData['updated_user']	= !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
				$aData['updated_time']	= time();
				$aData['to_arc'] 		= 0;
					
				$oObjectServices->updateSingle( $aData );
				
				$oResponse->setFormElement('form1', 'formDataOK', array('value' => 'yes'), '');	
			}

			$oResponse->printResponse();
		}
			
	}
	
?>