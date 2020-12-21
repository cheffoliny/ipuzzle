<?php

	class ApiObjectToLimitCard  {
		
		public function load( DBResponse $oResponse) {			
			$nIDContract	= Params::get('nID','0');
			
			$oDBContracts = new DBContracts();
			$oDBTemplets = new DBTemplets();
			$oDBStatuses = new DBStatuses();
			
			$proba = $oDBTemplets->getmaxID();
			
			$aTemplets = $oDBTemplets->getTemplets();
		
			$oResponse->setFormElement('form1', 'nIDTemplets', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDTemplets', array_merge(array("value"=>'0')), "--Изберете--");
			foreach($aTemplets as $key => $value) {
				$value = trim($value);
				$value = iconv("cp1251","utf-8",$value);
				$oResponse->setFormElementChild('form1', 'nIDTemplets', array_merge(array("value"=>$key)), $value);
			}
			
			$aContract = $oDBContracts->getRecord($nIDContract);
			
			$oResponse->setFormElement('form1', 'sNameNew', array(), $aContract['obj_name']);	
			
			$aStatuses = $oDBStatuses->getStatuses();
			
			$oResponse->setFormElement('form1', 'nIDStatus', array(), '');
			foreach ($aStatuses as $key => $value) {
				$oResponse->setFormElementChild('form1','nIDStatus',array("value" => $key),$value);
			}
			
			$oResponse->printResponse();
		}
		
		public function attachNewObject( DBResponse $oResponse) {
			$nIDContract	= Params::get('nID','0');
			$nIDTemplet 	= Params::get('nIDTemplets','0');
			$nNum			= Params::get('nNumNew','');
			$sName			= Params::get('sNameNew','');
			$nIDLimitCard 	= Params::get('id_limit_card','');
			$nIDStatus		= Params::get('nIDStatus','1');
		
			$sNameUTF 		= $sName;	
			
			if( empty($nNum) ) {
				throw new Exception("Въведете номер на новия обект!");
			}
			
			if( empty($sName) ) {
				throw new Exception("Въведете име на новия обект!");
			}
			
			if( empty($nIDTemplet) ) {
				throw new Exception("Изберете шаблон от сигнали за новия обект!");
			}
		
			$oDBObjects 		= new DBObjects();
			$oDBObjects2 		= new DBObjects2();
			$oDBContracts 		= new DBContracts();
			$oDBMessages 		= new DBMessages2();
			$oDBTechLimitCards 	= new DBTechLimitCards();
			$oDBContractsServices = new DBContractsServices();
			$oDBSingles 		= new DBSingles();
			$oDBServices 		= new DBServices();
			$oDBLastId 			= new DBLastId();	
			$oDBFaces 			= new DBFaces();
			$oDBPPP 			= new DBPPP();
			$oDBRegions2 		= new DBRegions2();
			$oDBContractsFaces 	= new DBContractsFaces();
			$oDBFaces2 			= new DBFaces2();
			$oDBObjSingles		= new DBObjectsSingles();
			$oDBObjServices		= new DBObjectServices();
			$oDBNomServices		= new DBNomenclaturesServices();
			
			$aLimitCard = array();
			$aLimitCard = $oDBTechLimitCards->getRecord($nIDLimitCard);
			
			if ( !empty($aLimitCard['id_object']) ) {
				throw new Exception('Обекта вече е привързан към лимитната карта');
			}
			
			$aContract 		= array();
			$aSingles 		= array();		
				
			$aContract 		= $oDBContracts->getRecord($nIDContract);
			$nMonthAccount 	= $oDBContractsServices->countMonthAccount($nIDContract);
			$aSingles 		= $oDBContractsServices->getSingles($nIDContract);
			$aServices 		= $oDBContractsServices->getMonthWithoutBase($nIDContract);	

			$nStacButton 	= '0';
			$nRadioButton 	= '0';
			$nKbButton 		= '0';
			
			foreach ($aServices as $key => $value ) {
				switch ($value['service_type']) {
					case 'panic_stat'	: $nStacButton 	= '1'; break;
					case 'panic_radio'	: $nRadioButton = '1'; break;
					case 'panic_kbd'	: $nKbButton 	= '1'; break;
				}
			}
			
			
			$nIDRegion = $oDBRegions2 -> getIDRegion($aContract['id_office']);
			$aContractsFaces = $oDBContractsFaces->getFaces($nIDContract);
 			
			
			$aContractLatin = $aContract;
			foreach ($aContractLatin AS $key => &$value) {
				$value = trim($value);
				$value = iconv('UTF-8', 'cp1251', $value);  			
			}
			$sName = trim($sName);
			
			$sTypeName = '';
			
			switch ($aContract['contract_type']) {
				case 'mdo': $sTypeName = 'Месечна денонщна охрана';	break;
				case 'mon': $sTypeName = 'Мониторинг';	break;
				default	  : $sTypeName = '';
			}
			
			$sTypeName = trim($sTypeName);
			$sTypeName = iconv('UTF-8','cp1251',$sTypeName);
			
			$aData2 = array();			
			$aData2['name'] 	= iconv('UTF-8', 'cp1251', $sName);   
			$aData2['num']  	= $nNum;
			$aData2['address'] 	= $aContractLatin['obj_address'];
			$aData2['phone'] 	= $aContractLatin['obj_phone'];
			$aData2['start'] 	= time();
			$aData2['info'] 	= $aContractLatin['info_operativ'].' '.$aContractLatin['info_tehnics'];
			$aData2['id_status'] = $nIDStatus;
			$aData2['id_region'] = $nIDRegion;
			$aData2['firm_name'] = $aContractLatin['client_name'];
			$aData2['tax_num'] 	= $aContractLatin['client_bul'];
			$aData2['bulstat'] 	= $aContractLatin['client_dn'];
			$aData2['address_reg'] = $aContractLatin['client_address'];
			$aData2['price'] 	= (5/6)*$nMonthAccount;
			$aData2['change_status'] = date('Y-m-d');
			$aData2['paid_month'] = date('Y-m-d',strtotime("-1 month"));
			$aData2['change_price'] = date('Y-m-d');
			$aData2['obj_info'] = $aContractLatin['info_schet'];
			$aData2['date_skl'] = $aContractLatin['contract_date'];
			$aData2['srok'] 	= $aContractLatin['period_in_month'];
			$aData2['date_vsila'] = $aContractLatin['contract_date'];
			$aData2['time_react'] = $aContractLatin['reaction_time_normal'];
			$aData2['plan'] 	= $sTypeName;
			$aData2['single_otg'] = $aContractLatin['single_liability'];
			$aData2['yearly_otg'] = $aContractLatin['year_liability'];
			$aData2['kod_rs'] 	= $aContractLatin['rs_code'];
				
			$aData2['stac_button'] 	= $nStacButton;
			$aData2['radio_button'] = $nRadioButton;
			$aData2['kb_button'] 	= $nKbButton; 
			
			
			if ( $aContract['technics_type'] == 'buy' ) {
				$aData2['tehnika_cena'] = (5/6)*$aContractLatin['technics_price'];
				$aData2['tehnika_broi'] = '1';
				$aData2['tehnika'] = 'buy';
			}
			
			if($aContract['client_is_company'] == '1') {
				$aData2['presentedby'] = $aContractLatin['client_mol'];
			} else {
				$aData2['presentedby'] = $aContractLatin['client_name'];
			}
			$aData2['is_service_mode'] = '1';
			$aData2['service_mode_time'] = time();
			
			
			$oDBObjects2->update($aData2);   // Създаване на обекта в базата на Power Link
			
			
			foreach ($aContractsFaces as $key => $value) {
				$sName = trim($value['name']);
				$sPhone = trim($value['phone']);
				$sName = iconv("utf-8","cp1251",$sName);
				$sPhone = iconv("utf-8","cp1251",$sPhone);
				$aOFaces = array();
				$aOFaces['id_obj'] = $aData2['id'];
				$aOFaces['name'] = $sName;
				$aOFaces['phone'] = $sPhone;
				$oDBFaces2->update($aOFaces);			// Вкарване на МОЛ-овете в старата база
			}
			$aOFaces = array();
			$aOFaces['id_obj'] = $aData2['id'];
			if($aContract['client_is_company']) {
				$aOFaces['name'] = $aContractLatin['client_mol'];
			} else {
				$aOFaces['name'] = $aContractLatin['client_name'];
			}
			$aOFaces['phone'] = $aContractLatin['client_phone'];
			$aOFaces['own'] = '1';
			$oDBFaces2->update($aOFaces); 						
			
			$oDBObjects2->setMOL($aData2['id'],$aOFaces['id']);
			
			$oDBMessages -> insertMessages($nIDTemplet, $aData2['id']); // Добавяне на сигнали към създадения обект
			
			$nToTechnicsPrice = 0;
			foreach ($aSingles as $key => $value) {
				
				/*if( $value['service_type'] == 'single_other' && empty($value['service_name'])) {
					$nToTechnicsPrice += (5/6)*$value['price'];
				}*/
				$aSin = array();
				$aSin['id_obj'] = $aData2['id'];
				$aSin['info'] = iconv("utf-8","cp1251",trim($value['service_name']));
				$aSin['sum'] = (5/6)*$value['price'];
				//$aSin['sum_p'] = $value['price'];
				//$aSin['nomral'] = '1';
				$aSin['data'] = date('Y-m-d');
				$aSin['currency'] = "BGL";
				$oDBSingles->update($aSin);
			}												// Добавяне на еднократни задължения в базата на Power Link
			
			if($aContract['technics_type'] == 'buy') {
				
				$sInfo = "Комплект СОT";
				
				$aSin = array();
				$aSin['id_obj'] = $aData2['id'];
				$aSin['info'] = iconv("utf-8","cp1251",trim($sInfo));
				$aSin['sum'] = (5/6)*$aContractLatin['technics_price'];
				//$aSin['sum_p'] = $value['price'];
				//$aSin['nomral'] = '1';
				$aSin['data'] = date('Y-m-d');
				$aSin['currency'] = "BGL";
				$oDBSingles->update($aSin);
			}
			
			if(!empty($nToTechnicsPrice)) {
				$oDBObjects2->increaseTechnicsPrice($nToTechnicsPrice,$aData2['id']);
			}
			
			
			foreach ($aServices as $key => $value) {
				$nLastId = $oDBLastId->getServicesLastId();
				$aServ = array();
				$aServ['id_service'] = $nLastId+1;
				$aServ['id_obj'] = $aData2['id'];
				$aServ['id_type'] = 0;
				$aServ['price'] = (5/6)*$value['price'];
				$aServ['name'] = iconv("utf-8","cp1251",trim($value['user_name']));
				$aServ['gsm'] = $value['user_gsm'];
				$aServ['mail'] = $value['user_email'];
				$oDBServices->update($aServ);
				$oDBLastId->plusplusServicesLastId();
			}										// Добавяне на допълнителни месечи задължения в базата на Power Link
			
			$aNewObject = array();
			$aNewObject['id_oldobj'] 	= $aData2['id'];
			$aNewObject['id_client'] 	= 0;
			$aNewObject['id_objtype'] 	= 93;
			$aNewObject['id_function'] 	= 7;
			$aNewObject['change_status']= time();
			$aNewObject['change_price'] = time();
			$aNewObject['invoice_name'] = $sNameUTF;
			$aNewObject['email']	 	= $aContract['client_email'];
			$aNewObject['price']	 	= $nMonthAccount;
			$aNewObject['num'] 			= $nNum;
			$aNewObject['name'] 		= $sNameUTF;
			$aNewObject['id_status'] 	= $nIDStatus;
			$aNewObject['id_office'] 	= $aContract['id_office'];
			$aNewObject['id_reaction_office'] = $aContract['id_office'];
			$aNewObject['id_tech_office'] = $aContract['id_office'];
			$aNewObject['is_sod'] 		= 1;
			$aNewObject['address'] 		= $aContract['obj_address'];
			$aNewObject['phone'] 		= $aContract['obj_phone'];
			$aNewObject['start'] 		= time();
			$aNewObject['distance'] 	= $aContract['obj_distance'];
			
			$oDBObjects->update($aNewObject);    //Създаване на обекта в базата на Теленет
			
			
			// Tehnika v Telenet
			if ( $aContract['technics_type'] == 'buy' ) {
				$aNService 	= array();
				$aSData 	= array();
				
				$aNService = $oDBNomServices->getDefault(0);
				
				$aSData['id_object'] 	= $aNewObject['id'];
				$aSData['id_office'] 	= $aContract['id_office'];
				$aSData['id_service'] 	= isset($aNService['id']) ? $aNService['id'] : 0;
				$aSData['service_name']	= "Комплект СОT";
				$aSData['single_price'] = $aContractLatin['technics_price'];
				$aSData['quantity'] 	= 1;
				$aSData['total_sum'] 	= $aContractLatin['technics_price'];
				$aSData['start_date'] 	= time();
				$aSData['paid_date'] 	= '0000-00-00';
				$aSData['id_sale_doc'] 	= 0;
				
				$oDBObjSingles->update( $aSData );
			}	

			// Ednokratni zadyljeniq v Telenet
			$aNomSingles = array();
			$aNomSingles = $oDBContractsServices->getTaxes( $nIDContract, 1 );
			
			foreach ( $aNomSingles as $val ) {
				$aSData 				= array();
				$aSData['id_object'] 	= $aNewObject['id'];
				$aSData['id_office'] 	= $aContract['id_office'];
				$aSData['id_service'] 	= isset($val['id_nomenclatures_service']) ? $val['id_nomenclatures_service'] : 0;
				$aSData['service_name']	= isset($val['name']) 		? $val['name'] 		: "";
				$aSData['single_price'] = isset($val['price']) 		? $val['price'] / $val['quantity'] : 0;
				$aSData['quantity'] 	= isset($val['quantity']) 	? $val['quantity'] 	: 0;
				$aSData['total_sum'] 	= isset($val['price']) 		? $val['price'] 	: 0;
				$aSData['start_date'] 	= time();
				$aSData['paid_date'] 	= '0000-00-00';
				$aSData['id_sale_doc'] 	= 0;
				
				$oDBObjSingles->update( $aSData );				
			}
			
			// Mesechni zadyljeniq v Telenet
			$aNomSingles = array();
			$aNomSingles = $oDBContractsServices->getTaxes( $nIDContract, 0 );
			
			foreach ( $aNomSingles as $val ) {
				$aSData 				= array();
				$aSData['id_object'] 	= $aNewObject['id'];
				$aSData['id_office'] 	= $aContract['id_office'];
				$aSData['id_service'] 	= isset($val['id_nomenclatures_service']) ? $val['id_nomenclatures_service'] : 0;
				$aSData['id_schet'] 	= 0;
				$aSData['service_name']	= isset($val['name']) 		? $val['name'] 		: "";
				$aSData['single_price'] = isset($val['price']) 		? $val['price'] / $val['quantity'] : 0;
				$aSData['quantity'] 	= isset($val['quantity']) 	? $val['quantity'] 	: 0;
				$aSData['total_sum'] 	= isset($val['price']) 		? $val['price'] 	: 0;
				$aSData['start_date'] 	= time();
				$aSData['last_paid'] 	= '0000-00-00';
				$aSData['id_sale_doc'] 	= 0;
				
				$oDBObjServices->update( $aSData );				
			}			
			
			// MOL
			$mol = !empty($aContract['client_mol']) ? $aContract['client_mol'] : $aContract['client_name'];
			
			if ( !empty($mol) ) {
				$aFace = array();
				$aFace['id_obj'] 	= $aNewObject['id'];
				$aFace['name'] 		= $mol;
				$aFace['phone'] 	= $aContract['client_phone'];
				
				$oDBFaces->update($aFace);       // копиране на мола от договор към обект
				$oDBObjects->setIdFace( $aFace['id'], $aNewObject['id'] );
			}
			
			// Faces
			$oDBFaces->fromContract( $aNewObject['id'], $nIDContract ); //копиране на всички молове
			
			$aData = array();
			$aData['id'] 				= $nIDContract;
			$aData['id_obj'] 			= $aNewObject['id'];
			$aData['contract_status'] 	= 'validated';
			
			$oDBContracts->update($aData);         // Привързване на обекта към Електронния договор и смяна на статуса на договора на "валидиран" 

			// Clienti - pravim proverka dali syshtestvuva
			// 1. Da - pryvyrzvame oekt-a kym nego
			// 2. Ne - syzdavame nov klient
			$aClientData = array();
			$oValidate = new Validate();
			$oClients  = new DBClients();
			
			if ( $aContract['is_invoice'] == 0 ) {
				$pay = "receipt";
			} else {
				$pay = $aContract['pay_cash'] == 1 ? "cash" : "bank";
			}
			
			if ( $aContract['client_is_company'] == 1 ) {
				$aClientData['invoice_mol'] 		= $aContract['client_mol'];
				$aClientData['invoice_recipient'] 	= $aContract['client_mol'];
			} else {
				
				$aClientData['invoice_mol'] 		= $aContract['client_name'];
				$aClientData['invoice_recipient'] 	= $aContract['client_name'];		
			}
			
			$aClientData['name'] 				= $aContract['client_name'];
			$aClientData['address'] 			= $aContract['client_address'];
			$aClientData['email'] 				= $aContract['client_email'];
			$aClientData['phone'] 				= $aContract['client_phone'];
			$aClientData['invoice_address']	 	= $aContract['client_address'];	
			$aClientData['invoice_layout'] 		= "single";
			$aClientData['invoice_payment'] 	= $pay;
			$aClientData['invoice_email'] 		= "";
			
			if ( !empty($aContract['client_egn']) ) {
				$oValidate->variable = $aContract['client_egn'];
				$oValidate->checkEGN();
				
				if ( $oValidate->result ) {
					$aClientData['invoice_ein'] 	= $aContract['client_egn'];
					$aClientData['invoice_ein_dds'] = "";
					
					if ( $oClients->isEINUnique($aContract['client_egn']) ) {
						$oClients->update( $aClientData );
						
						$nIDClient 	= $aClientData['id'];
					} else {
						$tmpArr 	= $oClients->getClientByEIN( $aContract['client_egn'] );
						$nIDClient 	= isset($tmpArr['id']) ? $tmpArr['id'] : 0;
					}
					
					$oClients->attachObjectToClient( $nIDClient, $aNewObject['id'] );			
				}
			} elseif ( !empty($aContract['client_dn']) ) {
				$oValidate->variable = $aContract['client_dn'];
				$oValidate->checkEIN();
				
				if ( $oValidate->result ) {
					$aClientData['invoice_ein'] 	= $aContract['client_dn'];
					$aClientData['invoice_ein_dds'] = "BG".$aContract['client_dn'];
					
					if ( $oClients->isEINUnique($aContract['client_dn']) ) {
						$oClients->update( $aClientData );
						
						$nIDClient 	= $aClientData['id'];
					} else {
						$tmpArr 	= $oClients->getClientByEIN( $aContract['client_dn'] );
						$nIDClient 	= isset($tmpArr['id']) ? $tmpArr['id'] : 0;						
					}
					
					$oClients->attachObjectToClient( $nIDClient, $aNewObject['id'] );	
				}				
			}
			

				
			$oDBTechLimitCards->attachObject( $nIDLimitCard, $aNewObject['id'] );  // Привързване на обекта към лимитната карта
		
			$oDBPPP->setDestObject( $nIDLimitCard, $aNewObject['id'] ); 
			
			$oResponse->setFormElement('form1', 'id_object', array(), $aNewObject['id']);
			
			$oResponse->printResponse();	
		}
		
		public function attachExistingObject( DBResponse $oResponse) {
			$nIDObject 		= Params::get('id_object','0');
			$nIDContract	= Params::get('nID','0');
			$nIDLimitCard = Params::get('id_limit_card','0');
			
			if( empty($nIDObject) ) {
				throw new Exception("Не сте избрали правилно обект за привързване!");
			}
		
			$oDBContracts = new DBContracts();
			$oDBTechLimitCards = new DBTechLimitCards();
			$oDBObjects = new DBObjects();
			$oDBObjects2 = new DBObjects2();
			$oDBPPP = new DBPPP();
			$oDBFaces = new DBFaces();
			
			$aObject = $oDBObjects->getRecord($nIDObject);
			$aContracts = $oDBContracts->getRecord($nIDContract);
			
			$oDBFaces->deleteFaces($nIDObject);
			
			$aFace = array();
			$aFace['id_obj'] = $nIDObject;
			$aFace['name'] = $aContracts['client_mol'];
			$aFace['phone'] = $aContracts['client_phone'];
			$oDBFaces -> update($aFace);       // копиране на мола от договор към обект
			
			$oDBFaces -> fromContract($nIDObject,$nIDContract); //копиране на всички молове
			
			$aObject['name'] = $aContracts['obj_name'];
			$aObject['phone'] = $aContracts['obj_phone'];
			$aObject['id_face'] = $aFace['id'];
			$oDBObjects->update($aObject);
			
			if(!empty($aObject['id_oldobj'])) {
				$oDBObjects2->setServiceStatus($aObject['id_oldobj']);
			}
			
			$aData = array();
			$aData['id'] = $nIDContract;
			$aData['id_obj'] = $nIDObject;
			$aData['contract_status'] = 'validated';
			$oDBContracts -> update($aData);         // Привързваме обекта към Електронния договор и смяна на статуса на договора на "валидиран"
		
			$oDBTechLimitCards->attachObject($nIDLimitCard,$nIDObject); // Привързване на обекта към лимитната карта
			
			$oDBPPP->setDestObject($nIDLimitCard,$nIDObject); 
			
			$oResponse->printResponse();	
		}
	
	}

?>