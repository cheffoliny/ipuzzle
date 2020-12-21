<?php
	require_once("pdf/pdf_sale_doc.php");

	class ApiSaleDocInfo {
		
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			$sViewType = Params::get('sViewType','');		
			$sApiAction = Params::get('api_action','');
			$sDocStatus = Params::get('sDocStatus','');
			
			// Павел
			$oResponse->setFormElement( 'form1', 'bank_acc', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'bank_acc', array('id' => 0), 'Изберете сметка' );
			
			if(!empty($nID)) {
				
				switch ($sDocStatus) {
					
					case 'canceled':
						$oResponse->setFormElement('form1','page_caption',array("background-color"=> "red"));
						$oResponse->setFormElement('form1','b_del',array('disabled' => 'disabled'));
					case 'final':
						$oResponse->setFormElement('form1','editDocDate',array('disabled' => 'disabled'));
						
						if(!in_array('sale_doc_super_edit',$_SESSION['userdata']['access_right_levels']) || $sDocStatus == 'canceled') {
						
							$oResponse->setFormElement('form1','client_name',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','client_address',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','client_ein',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','client_ein_dds',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','client_mol',array('disabled' => 'disabled'));	
							$oResponse->setFormElement('form1','deliverer_name',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','deliverer_address',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','deliverer_ein',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','deliverer_ein_dds',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','deliverer_mol',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','note',array('disabled' => 'disabled'));
							$oResponse->setFormElement('form1','b_save',array('disabled' => 'disabled'));
						
						}
						
						$oResponse->setFormElement('form1','sDocDate',array('disabled' => 'disabled'));
						$oResponse->setFormElement('form1','paid_type',array('disabled' => 'disabled'));
						$oResponse->setFormElement('form1','bank_acc',array('disabled' => 'disabled'));
						$oResponse->setFormElement('form1','client_recipient',array('disabled' => 'disabled'));
						$oResponse->setFormElement('form1','note',array('disabled' => 'disabled'));
						
						$oResponse->setFormElement('form1','b_confirm',array('disabled' => 'disabled'));
						
					break;					
				}
				
				if($sApiAction == 'export_to_pdf') {
					$sPrintType = Params::get('print_type','');
					
					switch ($sPrintType) {
						case 'original':
							$sPrintType = 'ОРИГИНАЛ';
						break;
						case 'normal':
							$sPrintType = '';
						break;
						case 'copy':
							$sPrintType = 'КОПИЕ';
						break;

						case 'two':
							$sPrintType = 'two';
						break;	
												
						default:
							$sPrintType = 'two';
					}
					
					$oDBSaleDocPDF = new SaleDocPDF("P");
					$oDBSaleDocPDF->PrintReport($nID,$sPrintType,$sViewType);
				}
				
				
				$oDBSalesDocs = new DBSalesDocs();
				$oDBSalesDocsRows = new DBSalesDocsRows();
						
				$aSaleDoc 	= $oDBSalesDocs->getDoc($nID);
				
				// Павел
				$aBank = $oDBSalesDocs->getBankByDoc( $nID );
				
				if ( isset($aBank['id_bank']) && !empty($aBank['id_bank']) ) {
					$aBanks = array();
					$nIDFirm = isset($aBank['id_firm']) ? $aBank['id_firm'] : 0;
					$aBanks = $oDBSalesDocs->getBankByFirm( $nIDFirm );
					//APILog::Log(0, $aBank);
					foreach ( $aBanks as $val ) {
						if ( $val['id'] == $aSaleDoc['id_bank_account'] ) {
							$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id'], 'selected' => 'selected'), $val['name_account'] );
						} else {
							$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id']), $val['name_account'] );
						}
					}
					
				}
				
				$oResponse->setFormElement('form1','client_name',array(),$aSaleDoc['client_name']);
				$oResponse->setFormElement('form1','client_address',array(),$aSaleDoc['client_address']);
				$oResponse->setFormElement('form1','client_ein',array(),$aSaleDoc['client_ein']);
				$oResponse->setFormElement('form1','client_ein_dds',array(),$aSaleDoc['client_ein_dds']);
				$oResponse->setFormElement('form1','client_mol',array(),$aSaleDoc['client_mol']);
				
				$oResponse->setFormElement('form1','deliverer_name',array(),$aSaleDoc['deliverer_name']);
				$oResponse->setFormElement('form1','deliverer_address',array(),$aSaleDoc['deliverer_address']);
				$oResponse->setFormElement('form1','deliverer_ein',array(),$aSaleDoc['deliverer_ein']);
				$oResponse->setFormElement('form1','deliverer_ein_dds',array(),$aSaleDoc['deliverer_ein_dds']);
				$oResponse->setFormElement('form1','deliverer_mol',array(),$aSaleDoc['deliverer_mol']);
			
				if(empty($sViewType)) {
					$sViewType = !empty($aSaleDoc['view_type']) ? $aSaleDoc['view_type'] : 'single';
				}
				
				$oDBSalesDocsRows->getReport2($oResponse,$nID,$sViewType);
				$oResponse->setFormElement('form1','view_type',array("value" => $sViewType));
				
				$oResponse->setFormElement('form1','sDocDate',array(),mysqlDateToJsDate($aSaleDoc['doc_date']));
					
				$sPaidType = !empty($aSaleDoc['paid_type']) ? $aSaleDoc['paid_type'] : 'cash';
				$oResponse->setFormElement('form1','paid_type',array('value' => $sPaidType));
				if($aSaleDoc['total_sum'] == $aSaleDoc['orders_sum'] && $aSaleDoc['total_sum'] != '0.00' ) {
					$oResponse->setFormElement('form1','paid_status',array(),'погасен');
				} elseif( $aSaleDoc['orders_sum'] != '0.00') {
					$oResponse->setFormElement('form1','paid_status',array(),'частично погасен');
				} else {
					$oResponse->setFormElement('form1','paid_status',array(),'непогасен');
				}
				
				$oResponse->setFormElement('form1','note',array(),$aSaleDoc['note']);
				
				$oResponse->setFormElement('form1','client_recipient',array(),$aSaleDoc['client_recipient']);
				$oResponse->setFormElement('form1','created_user',array(),$aSaleDoc['created']);
				$oResponse->setFormElement('form1','updated_user',array(),$aSaleDoc['updated']);
				APILog::Log(0, $aSaleDoc);
			}
			
			$oResponse->printResponse("Документ за продажба",'sale_doc');
		}
		
		public function change_view(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			$sViewType = Params::get('view_type','singel');
			
			$oDBSalesDocsRows = new DBSalesDocsRows();
			$oDBSalesDocsRows->getReport2($oResponse,$nID,$sViewType);
			
			$oResponse->printResponse();
		}
		
		public function del_doc(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			
			$oDBSalesDocs 		= new DBSalesDocs();
			$oDBSalesDocsRows 	= new DBSalesDocsRows();
			$oDBOrders 			= new DBOrders();
			$oDBObjectServices 	= new DBObjectServices();
			$oDBObjectSingle 	= new DBObjectsSingles();
			$oDBNomenclaturesServices = new DBNomenclaturesServices();
			$oDBAccountStates 	= new DBAccountStates();
			$oDBSystem 			= new DBSystem();
			$oSyncMoney 		= new DBSyncMoney();
			
			/* Анулирвам документа за продажба */
			
			$aSaleDoc = array();
			$aSaleDoc['id'] = $nID;
			$aSaleDoc['doc_status'] = 'canceled';
			
			$oDBSalesDocs->update($aSaleDoc);
			
			$oDBSalesDocs->getRecord($nID, $aSaleDoc);
			
			$nIDSchet = isset($aSaleDoc['id_schet']) ? $aSaleDoc['id_schet'] : 0;
			
			if ( !empty($nIDSchet) ) {
				$oSyncMoney->invalidate( $nIDSchet );
			}
			
		
			if($aSaleDoc['doc_type'] != "kreditno izvestie" && $aSaleDoc['doc_type'] != "debitno izvestie") {
			
				/* Връщам старите падежи на услугите по обектите */
					
				$aRowsFirstMonthsPaid = $oDBSalesDocsRows->getFirstPaidMonths($nID);
				$oDBSalesDocs->returnOldObjectLastPaids($aRowsFirstMonthsPaid);
				APILog::Log(0, $aRowsFirstMonthsPaid);
				/* Ако към документа за продажба има приходни ордери то създавам разходни */
				
				$aOrderParams = array();
				$aOrderParams['id_doc'] = $nID;
				$aOrderParams['doc_type'] = 'sale';
				$aOrderParams['order_type'] = 'earning';
				
				$aOrders = $oDBOrders->getOrders($aOrderParams);
			
				foreach ($aOrders as $aOrder) {
					if(floatval($aOrder['order_sum']) > 0) {
						
						$nExpenseSum = -$aOrder['order_sum'];
						
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
							$aAccountState['current_sum'] += $nExpenseSum;
						} else {
							$aAccountState['current_sum'] = $nExpenseSum;
						}
						$oDBAccountStates->update($aAccountState);
						
						
						unset($aOrder['id']);
						unset($aOrder['note']);
						
						$aOrder['num'] 			= $nNumOrder;
						$aOrder['order_type'] 	= 'earning';
						$aOrder['order_date'] 	= time();
						$aOrder['order_sum'] 	= $nExpenseSum;
						$aOrder['account_sum'] 	= $aAccountState['current_sum'];
						$aOrder['created_user'] = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
						$aOrder['created_time'] = time();
						
						$oDBOrders->update($aOrder);
						$oDBSystem->setLastNumOrder($nNumOrder);
					}
				}
			}
			$oResponse->setFormElement('form1','sDocStatus',array(),"canceled");
			$oResponse->printResponse();
			
		}
		
		public function save(DBResponse $oResponse) {
			
			$nID 				= Params::get('nID',0);
			$sDocStatus 		= Params::get('sDocStatus','');
			$nIDClient 			= Params::get('id_client','');
			$sClientName 		= Params::get('client_name','');
			$sClientAddress 	= Params::get('client_address','');
			$sClientEin			= Params::get('client_ein','');
			$sClientEinDds 		= Params::get('client_ein_dds','');
			$sClientMol 		= Params::get('client_mol','');
			$sDelivererName 	= Params::get('deliverer_name','');
			$sDelivererAddress 	= Params::get('deliverer_address','');
			$sDelivererEin 		= Params::get('deliverer_ein','');
			$sDelivererEinDds 	= Params::get('deliverer_ein_dds','');
			$sDelivererMol 		= Params::get('deliverer_mol','');
			$sViewType 			= Params::get('view_type','single');
			$sDocDate 			= Params::get('sDocDate','');
			$sPaidType 			= Params::get('paid_type','cash');
			$nBankAcc 			= Params::get('bank_acc', 0);
			$sClientRecipient 	= Params::get('client_recipient','');
			$sNote 				= Params::get('note','');
			$sConfirmClick		= Params::get('confirm_click','false');
			
			if(!in_array($sViewType,array("single","detail","by_objects","by_services"))) {
				$sViewType = 'single';
			}
			
			$sDocDate = !empty($sDocDate) ? jsDateToTimestamp($sDocDate) : '0000-00-00';
			$sPaidType = in_array($sPaidType,array("cash","bank")) ? $sPaidType : 'cash';
			
			if(strtotime("-8 days") > $sDocDate) {
				throw new Exception("Датата на документа трябва да е най-много -7 дена от текущата дата");
			}
			
			$oDBSalesDocs = new DBSalesDocs();		
			$oDBSalesDocsRows = new DBSalesDocsRows();	
			$oDBClientsObjects = new DBClientsObjects();
			
			$aData = array();
			
			$oDBSalesDocs->getRecord($nID,$aData);
			
			if( ($sDocStatus == 'final' || $sConfirmClick == 'true') && $aData['doc_type'] == 'faktura' ) {
				
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
				
				$oDBClients = new DBClients();
				
				$aClient = array();
				
				if(!empty($nIDClient)) {
					$aClient = $oDBClients->getRecord($nID);
				} else {
					$aClient = $oDBClients->getClientByEIN($sClientEin);
				}
				
				$aClient['name'] = $sClientName;
				$aClient['invoice_address'] = $sClientAddress;
				$aClient['invoice_ein'] = $sClientEin;
				$aClient['invoice_ein_dds'] = $sClientEinDds;
				$aClient['invoice_mol'] = $sClientMol;
				
				$oDBClients->update($aClient);
				
				$nIDClient = $aClient['id'];
				
				$aObjectIDs = $oDBSalesDocsRows->getObjectsIDs($nID);
				
				foreach ($aObjectIDs as $value) {
					$nIDClientTmp = $oDBClientsObjects->getIDClientByIDObject($value['id_object']);
					
					if(empty($nIDClientTmp)) {
						$aClientObject = array();
						$aClientObject['id_client'] = $nIDClient;
						$aClientObject['id_object'] = $value['id_object'];
						$aClientObject['attach_date'] = time();
						
						$oDBClientsObjects->update($aClientObject);
					}
					
				}
				
			}
			
			if($sConfirmClick == 'true') {
				$aData['doc_status'] = 'final';
			}
			
			$aData['client_name'] 		= $sClientName;
			$aData['client_address'] 	= $sClientAddress;
			$aData['client_ein'] 		= $sClientEin;
			$aData['client_ein_dds'] 	= $sClientEinDds;
			$aData['client_mol'] 		= $sClientMol;
			$aData['deliverer_name'] 	= $sDelivererName;
			$aData['deliverer_address'] = $sDelivererAddress;
			$aData['deliverer_ein'] 	= $sDelivererEin;
			$aData['deliverer_ein_dds'] = $sDelivererEinDds;
			$aData['deliverer_mol'] 	= $sDelivererMol;
			$aData['view_type'] 		= $sViewType;
			$aData['doc_date'] 			= $sDocDate;
			$aData['paid_type'] 		= $sPaidType;
			$aData['id_bank_account'] 	= $nBankAcc;
			$aData['client_recipient'] 	= $sClientRecipient;
			$aData['note'] 				= $sNote;
			
			if(!empty($nIDClient)) {
				$aData['id_client'] = $nIDClient;
			}
			//APILog::Log(0, $aData);
			$oDBSalesDocs->update($aData);
			
			// Pavel
			$nIDSchet 	= isset($aData['id_schet']) ? $aData['id_schet'] : 0;
			$sType 		= isset($aData['doc_type']) ? $aData['doc_type'] : "kvitanciq";
			$sPaid 		= isset($aData['paid_type']) ? $aData['paid_type'] : "cash";
							
			switch ($sType) {
				case "kvitanciq": $nIDType = 0;
				break;
					
				case "faktura": $nIDType = 1;
				break;
					
				case "kreditno izvestie": $nIDType = 4;
				break;

				case "debitno izvestie": $nIDType = 3;
				break;
			}
							
			if ( !empty($nIDSchet) ) {
				$nIDAccount = isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account'] : 0;
				
				$oSyncMoney = new DBSyncMoney(); 
				$table 		= "mp".substr($nIDSchet, 0, 6);
				$id_schet	= intval( substr($nIDSchet, -7) );
				
				$aDataSchet 				= array();
				$aDataSchet['id'] 			= $id_schet;
				$aDataSchet['faktura_type'] = $nIDType;
				$aDataSchet['bank'] 		= $sPaid == "cash" ? 0 : 1;	
				
				if ( !empty($nIDAccount) ) {
					$oSyncMoney->updateSchetMonth( $aDataSchet );
				}				
			}
			

			
			if($sConfirmClick == 'true') {
				$oResponse->setFormElement('form1','open_order',array(),"true");
				$oResponse->setFormElement('form1','sDocStatus',array(),'final');
				$oResponse->printResponse();
			}
		}
	}

?>