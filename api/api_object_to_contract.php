<?php

	class ApiObjectToContract  {
		
		public function load( DBResponse $oResponse) {			
			$nIDContract	= Params::get('nID','0');
			
			$oDBContracts = new DBContracts();
			$oDBTemplets = new DBTemplets();
			
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
			
			$oResponse->printResponse();
		}
		
		public function attachNewObject( DBResponse $oResponse) {
			$nIDContract	= Params::get('nID','0');
			$nIDTemplet 	= Params::get('nIDTemplets','0');
			$nNum			= Params::get('nNumNew','');
			$sName			= Params::get('sNameNew','');
			
			if( empty($nNum) ) {
				throw new Exception("Въведете номер на новия обект!");
			}
			
			if( empty($sName) ) {
				throw new Exception("Въведете име на новия обект!");
			}
			
			if( empty($nIDTemplet) ) {
				throw new Exception("Изберете шаблон от сигнали за новия обект!");
			}
		
			$oDBObjects = new DBObjects();
			$oDBObjects2 = new DBObjects2();
			$oDBContracts = new DBContracts();
			$oDBMessages = new DBMessages2();
			$oDBTechLimitCards = new DBTechLimitCards();
			$oDBContractsServices = new DBContractsServices();
			$oDBSingles = new DBSingles();
			$oDBServices = new DBServices();
			
			$aContract = array();
			$aContract = $oDBContracts->getRecord($nIDContract);
			
			$nMonthAccount = $oDBContractsServices->countMonthAccount($nIDContract);
			$aSingles = array();
			$aSingles = $oDBContractsServices->getSingles($nIDContract);

			$aServices = $oDBContractsServices->getMonthWithoutBase($nIDContract);	
			
			$aNewObject = array();
			$aNewObject['num'] = $nNum;
			$aNewObject['name'] = $sName;
			$oDBObjects -> update($aNewObject);    //Създаване на обекта в базата на Теленет
			
			
			$aData = array();
			$aData['id'] = $nIDContract;
			$aData['id_obj'] = $aNewObject['id'];
			$aData['contract_status'] = 'validated';
			$oDBContracts -> update($aData);         // Привързване на обекта към Електронния договор и смяна на статуса на договора на "валидиран" 

			$aData2 = array();
			$sName = trim($sName);
			$aData2['name'] = iconv('UTF-8', 'cp1251', $sName);   
			$aData2['num']  = $nNum;
			$aData2['kod_rs'] = $aContract['rs_code'];
			$aData2['tehnika_cena'] = $aContract['technics_price'];
			$aData2['price'] = $nMonthAccount;
			$oDBObjects2->update($aData2);   // Създаване на обекта в базата на Powet Link
			
			$oDBMessages -> insertMessages($nIDTemplet, $aData2['id']); // Добавяне на сигнали към създадения обект
			
			foreach ($aSingles as $key => $value) {
				$aSin = array();
				$aSin['id_obj'] = $aData2['id'];
				$aSin['info'] = iconv("utf-8","cp1251",trim($value['service_name']));
				$aSin['sum'] = $value['price'];
				$aSin['sum_p'] = $value['price'];
				$aSin['currency'] = "BGL";
				$oDBSingles->update($aSin);
			}												// Добавяне на еднократни задължения в базата на Power Link
			
			
			foreach ($aServices as $key => $value) {
				$nLastId = $oDBLastId->getServicesLastId();
				$aServ = array();
				$aServ['id_service'] = $nLastId+1;
				$aServ['id_obj'] = $aData2['id'];
				$aServ['id_type'] = 0;
				$aServ['price'] = $value['price'];
				$aServ['name'] = iconv("utf-8","cp1251",trim($value['user_name']));
				$aServ['gsm'] = $value['user_gsm'];
				$aServ['mail'] = $value['user_email'];
				$oDBServices->update($aServ);
				$oDBLastId->plusplusServicesLastId();
			}										// Добавяне на допълнителни месечи задължения в базата на Power Link
			
			$aNewLimitCard = array();
			$aNewLimitCard['id_object'] = $aNewObject['id'];
			$aNewLimitCard['type'] = 'create';
			$oDBTechLimitCards->update($aNewLimitCard);  // Създаване на лимтна карта тип 'изграждане' за новия обект

			
			$oResponse->setFormElement('form1','id_limit_card',array(),$aNewLimitCard['id']); 
			$oResponse->printResponse();	
		}
		
		public function attachExistingObject( DBResponse $oResponse) {
			$nIDObject 		= Params::get('id_object','0');
			$nIDContract	= Params::get('nID','0');
			
			if( empty($nIDObject) ) {
				throw new Exception("Не сте избрали правилно обект за привързване!");
			}
		
			$oDBContracts = new DBContracts();
			$oDBTechLimitCards = new DBTechLimitCards();
					
			$aData = array();
			$aData['id'] = $nIDContract;
			$aData['id_obj'] = $nIDObject;
			$aData['contract_status'] = 'validated';
			$oDBContracts -> update($aData);         // Привързваме обекта към Електронния договор и смяна на статуса на договора на "валидиран"
		

			$aNewLimitCard = array();
			$aNewLimitCard['id_object'] = $nIDObject;
			$aNewLimitCard['type'] = 'create';
			$oDBTechLimitCards -> update($aNewLimitCard);  // Създаване на лимитна карта тип 'изграждане' за съшествуващия обект
			
			$oResponse->setFormElement('form1','id_limit_card',array(),$aNewLimitCard['id']);
			$oResponse->printResponse();	
		}
	
	}

?>