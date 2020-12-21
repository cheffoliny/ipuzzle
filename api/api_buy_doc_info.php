<?php

	class ApiBuyDocInfo {
		
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			$sViewType = Params::get('sViewType','');		
			$sDocStatus = Params::get('sDocStatus','');
			
			if(!empty($nID)) {
							
				switch ($sDocStatus) {
					case 'canceled':
						$oResponse->setFormElement('form1','page_caption',array('background-color' => 'red'));
						$oResponse->setFormElement('form1','b_del',array('disabled' => 'disabled'));
					case 'final':
						$oResponse->setFormElement('form1','editDocDate',array('disabled'=>'disabled'));	
						$oResponse->setFormElement('form1','sDocDate',array('disabled'=>'disabled'));
						$oResponse->setFormElement('form1','paid_type',array('disabled'=>'disabled'));
						$oResponse->setFormElement('form1','note',array('disabled'=>'disabled'));
						$oResponse->setFormElement('form1','b_confirm',array('disabled'=>'disabled'));			
					break;
				}

				$oDBBuyDocs = new DBBuyDocs();		
				
				$aBuyDoc = $oDBBuyDocs->getDoc($nID);
				
				$oResponse->setFormElement('form1','client_name',array(),$aBuyDoc['client_name']);
				$oResponse->setFormElement('form1','client_address',array(),$aBuyDoc['client_address']);
				$oResponse->setFormElement('form1','client_ein',array(),$aBuyDoc['client_ein']);
				$oResponse->setFormElement('form1','client_ein_dds',array(),$aBuyDoc['client_ein_dds']);
				$oResponse->setFormElement('form1','client_mol',array(),$aBuyDoc['client_mol']);
				
				$oResponse->setFormElement('form1','deliverer_name',array(),$aBuyDoc['deliverer_name']);
				$oResponse->setFormElement('form1','deliverer_address',array(),$aBuyDoc['deliverer_address']);
				$oResponse->setFormElement('form1','deliverer_ein',array(),$aBuyDoc['deliverer_ein']);
				$oResponse->setFormElement('form1','deliverer_ein_dds',array(),$aBuyDoc['deliverer_ein_dds']);
				$oResponse->setFormElement('form1','deliverer_mol',array(),$aBuyDoc['deliverer_mol']);
			
				if(empty($sViewType)) {
					$sViewType = $aBuyDoc['view_type'];
				}
				
				$oResponse->setFormElement('form1','sum_total',array(),sprintf('%0.2f лв.',$aBuyDoc['total_sum']));
				
				$oResponse->setFormElement('form1','sDocDate',array(),mysqlDateToJsDate($aBuyDoc['doc_date']));
					
				$oResponse->setFormElement('form1','paid_type',array('value' => $aBuyDoc['paid_type']));
				
				// Pavel
				// Stanislave: kogato proverqvash drobni chisla s empty vinagi se uverqvai che ne proverqvash string-a '0.00'!!!
				$aBuyDoc['total_sum'] = floatval($aBuyDoc['total_sum']);
				$aBuyDoc['orders_sum'] = floatval($aBuyDoc['orders_sum']);
				
				if($aBuyDoc['total_sum'] == $aBuyDoc['orders_sum'] && !empty($aBuyDoc['total_sum']) ) {
					$oResponse->setFormElement('form1','paid_status',array(),'погасен');
				} elseif(!empty($aBuyDoc['orders_sum'])) {
					$oResponse->setFormElement('form1','paid_status',array(),'частично погасен');
				} else {
					$oResponse->setFormElement('form1','paid_status',array(),'непогасен');
				}
				
				$oResponse->setFormElement('form1','note',array(),$aBuyDoc['note']);
				
				$oResponse->setFormElement('form1','created_user',array(),$aBuyDoc['created']);
				$oResponse->setFormElement('form1','updated_user',array(),$aBuyDoc['updated']);
			}
			
			$oResponse->printResponse("Документ за покупка",'buy_doc');
			
		}
		
		public function del_doc(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$oDBBuyDocs = new DBBuyDocs();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			$oDBOrders = new DBOrders();
			$oDBAccountStates = new DBAccountStates();
			$oDBSystem = new DBSystem();
			
			/* Анулирвам документа за продажба */
			
			$aBuyDoc = array();
			$aBuyDoc['id'] = $nID;
			$aBuyDoc['doc_status'] = 'canceled';
			$oDBBuyDocs->update($aBuyDoc);
			
			/* Ако има разходни ордери ги възтановявам с приходни */
			
			$aOrderParams = array();
			$aOrderParams['id_doc'] = $nID;
			$aOrderParams['doc_type'] = 'buy';
			$aOrderParams['order_type'] = 'expense';
			
			$aOrders = $oDBOrders->getOrders($aOrderParams);
			
			foreach ($aOrders as $aOrder) {
				if(floatval($aOrder['order_sum']) < 0) {
					
					$nEarningSum = -$aOrder['order_sum'];
					
					$aSystem = array();
					$aSystem = $oDBSystem->getRow();
					$nNumOrder = $aSystem['last_num_order'] + 1;
					
					$aAccountState = array();
					if(!empty($aOrder['bank_account_id'])) {
						$aAccountState = $oDBAccountStates->getRow('bank',0,$aOrder['bank_account_id']);
						$aAccountState['id_bank_account'] = $aOrder['bank_account_id'];
						$aAccountState['account_type'] = 'bank';
					} else {
						$aAccountState = $oDBAccountStates->getRow('person',$aOrder['id_person']);
						$aAccountState['id_person'] = $aOrder['id_person'];
						$aAccountState['account_type'] = 'person';
					}
					
					if(isset($aAccountState['current_sum']) && !empty($aAccountState['current_sum'])) {
						$aAccountState['current_sum'] += $nEarningSum;
					} else {
						$aAccountState['current_sum'] = $nEarningSum;
					}
					$oDBAccountStates->update($aAccountState);
					
					
					unset($aOrder['id']);
					unset($aOrder['note']);
					
					$aOrder['num'] = $nNumOrder;
					$aOrder['order_type'] = 'earning';
					$aOrder['order_date'] = time();
					$aOrder['order_sum'] = $nEarningSum;
					$aOrder['account_sum'] = $aAccountState['current_sum'];
					$aOrder['created_user'] = $nIDPerson;
					$aOrder['created_time'] = time();
					
					$oDBOrders->update($aOrder);
					$oDBSystem->setLastNumOrder($nNumOrder);
				}
			}
			
			/* Ако документа е тип заплата връщам старите стойности на полето изплатена сума */
			
			$oDBBuyDocs->getRecord($nID,$aBuyDoc);
			if($aBuyDoc['doc_type'] == 'salary') {
				$oDBSalary = new DBSalary();
				
				$aBuyDocRows = array();
				$aBuyDocRows = $oDBBuyDocsRows->getRowsByIDBuyDoc($nID);
				
				foreach ($aBuyDocRows as $aBuyDocRow) {
					$aSalaryRow = array();
					if(!empty($aBuyDocRow['id_salary_row'])) {
						$aSalaryRow = $oDBSalary->getRecord($aBuyDocRow['id_salary_row']);
						$aSalaryRow['paid_sum'] -= $aBuyDocRow['total_sum'];
						$oDBSalary->update($aSalaryRow);
					}
				}
			}
			
			$oResponse->setFormElement('form1','sDocStatus',array(),"canceled");
			$oResponse->printResponse();	
		}
		
		public function save(DBResponse $oResponse) {
			
			$nID				= Params::get('nID',0);
			$sDocStatus			= Params::get('sDocStatus','');
			$sClientName		= Params::get('client_name','');
			$sClientAddress		= Params::get('client_address','');
			$sClientEin			= Params::get('client_ein','');
			$sClientEinDds		= Params::get('client_ein_dds','');
			$sClientMol			= Params::get('client_mol','');
			$sDelivererName		= Params::get('deliverer_name','');
			$sDelivererAddress	= Params::get('deliverer_address','');
			$sDelivererEin		= Params::get('deliverer_ein','');
			$sDelivererEinDds	= Params::get('deliverer_ein_dds','');
			$sDelivererMol		= Params::get('deliverer_mol','');
			$sDocDate			= Params::get('sDocDate','');
			$sPaidType			= Params::get('paid_type','cash');
			$sNote				= Params::get('note','');
			$nIDDeliverer		= Params::get('id_deliverer','');
			$sConfirmClick		= Params::get('confirm_click','false');
			
			$sDocDate = !empty($sDocDate) ? jsDateToTimestamp($sDocDate) : '0000-00-00';
			$sPaidType = in_array($sPaidType,array("cash","bank")) ? $sPaidType : 'cash';
				
			if(strtotime("-8 days") > $sDocDate) {
				throw new Exception("Датата на документа трябва да е най-много -7 дена от текущата дата");
			}
			
			$oDBBuyDocs = new DBBuyDocs();
			
			$aData = array();
			$oDBBuyDocs->getRecord($nID,$aData);
			
			
			if( ($sDocStatus == 'final' ||  $sConfirmClick == 'true') && ($aData['doc_type'] == 'faktura' || $aData['doc_type'] == 'oprostena') ) {
				
				if(empty($sClientName)) {
					throw new Exception("Въведете име на клиента");
				}
				
				if(empty($sClientAddress)) {
					throw new Exception("Въведете адрес на клиента");
				}
				
				if(empty($sClientEin)) {
					throw new Exception("Въведете ЕИН на клиента");
				}
				
				if(empty($sClientMol)) {
					throw new Exception("Въведете МОЛ на клиента");
				}
				
				if(empty($sDelivererName)) {
					throw new Exception("Въведете име на доставчика");
				}
				
				if(empty($sDelivererAddress)) {
					throw new Exception("Въведете адрес на доставчика");
				}
				
				if(empty($sDelivererEin)) {
					throw new Exception("Въведете ЕИН на доставчика");
				}
				
				if(empty($sDelivererMol)) {
					throw new Exception("Въведете МОЛ на доставчика");
				}
			}
					
			if($sConfirmClick == 'true') {
				$aData['doc_status'] = 'final';
			}
			
			$aData['client_name'] = $sClientName;
			$aData['client_address'] = $sClientAddress;
			$aData['client_ein'] = $sClientEin;
			$aData['client_ein_dds'] = $sClientEinDds;
			$aData['client_mol'] = $sClientMol;
			$aData['id_deliverer'] = $nIDDeliverer;
			$aData['deliverer_name'] = $sDelivererName;
			$aData['deliverer_address'] = $sDelivererAddress;
			$aData['deliverer_ein'] = $sDelivererEin;
			$aData['deliverer_ein_dds'] = $sDelivererEinDds;
			$aData['deliverer_mol'] = $sDelivererMol;
			$aData['doc_date'] = $sDocDate;
			$aData['paid_type'] = $sPaidType;
			$aData['note'] = $sNote;
			
			$oDBBuyDocs->update($aData);
			
			if($sConfirmClick == 'true') {
				$oResponse->setFormElement('form1','open_order',array(),"true");
				$oResponse->setFormElement('form1','sDocStatus',array(),'final');
				$oResponse->printResponse();
			}
		}
	}

?>