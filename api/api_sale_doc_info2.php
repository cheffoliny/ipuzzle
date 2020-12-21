<?php
	require_once("pdf/pdf_sale_doc.php");

	class ApiSaleDocInfo2 {
		
		public function result(DBResponse $oResponse) {
			
			$nID 		= Params::get('nID',0);
			$sViewType 	= Params::get('sViewType','');		
			$sApiAction = Params::get('api_action','');
			$sDocStatus = Params::get('sDocStatus','');
			
			$oDBSalesDocs 		= new DBSalesDocs();
			$oDBSalesDocsRows 	= new DBSalesDocsRows();			
			
			// Павел
			$oResponse->setFormElement( 'form1', 'bank_acc', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'bank_acc', array('id' => 0), 'Изберете сметка' );
			
			if ( !empty($nID) ) {
				
				switch ($sDocStatus) {
					
					case 'canceled':
						$oResponse->setFormElement('form1', 'page_caption', array("background-color"=> "red"));
						$oResponse->setFormElement('form1', 'b_del', array('disabled' => 'disabled'));
					case 'final':
						$oResponse->setFormElement('form1', 'editDocDate', array('disabled' => 'disabled'));
						
						if ( !in_array('sale_doc_super_edit', $_SESSION['userdata']['access_right_levels']) || ($sDocStatus == 'canceled') ) {
						
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
					
					$oSaleDocTwoPDF = new SaleDocPDF("P");
					$oSaleDocTwoPDF->PrintReport($nID, $sPrintType, $sViewType);
				}
						
				$aSaleDoc 	= $oDBSalesDocs->getDoc($nID);
				$nIDClient	= isset($aSaleDoc['id_client']) ? $aSaleDoc['id_client'] : 0;
				
				// Павел
//				$aBank 		= $oDBSalesDocs->getBankByDoc( $nID );
//
//				if ( isset($aBank['id_bank']) && !empty($aBank['id_bank']) ) {
//					$aBanks = array();
//					$nIDFirm = isset($aBank['id_firm']) ? $aBank['id_firm'] : 0;
//					$aBanks = $oDBSalesDocs->getBankByFirm( $nIDFirm );
//					$aBanks = $oDBSalesDocs->getBanks();
//					
//					foreach ( $aBanks as $val ) {
//						if ( $val['id'] == $aSaleDoc['id_bank_account'] ) {
//							$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id'], 'selected' => 'selected'), $val['name_account'] );
//						} else {
//							$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id']), $val['name_account'] );
//						}
//					}
//				}
				
				$aBanks = $oDBSalesDocs->getBanks();
					
				foreach ( $aBanks as $val ) {
					if ( $val['id'] == $aSaleDoc['id_bank_account'] ) {
						$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id'], 'selected' => 'selected'), $val['name_account'] );
					} else {
						$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id']), $val['name_account'] );
					}
				}				
				
				$oResponse->setFormElement('form1', 'id_client',	array(), $nIDClient );
				$oResponse->setFormElement('form1','client_name',array(),$aSaleDoc['client_name']);
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
				
				$oDBSalesDocsRows->getReport2( $oResponse, $nID, $sViewType );
				
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
				//APILog::Log(0, $aSaleDoc);
			} else {
				$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
				$oDBClients = new DBClients();
				$oDBPerson  = new DBPersonnel();
				$aClient	= array();
				$sViewType 	= "single";
				$sPaidType	= "cash";
				
				$aPeson		= $oDBPerson->getByID( $nIDUser );
				$sUserTime 	= $aPeson['fname']." ".$aPeson['mname']." ".$aPeson['lname']." [".date("d.m.Y H:i:s")."]";				
				
				$oResponse->setFormElement( "form1", "created_user", 		array(), $sUserTime );
				$oResponse->setFormElement( "form1", "updated_user", 		array(), $sUserTime );	
				$oResponse->setFormElement( "form1", "sDocDate",  			array(), date("d.m.Y") );
				$oResponse->setFormElement( "form1", "paid_status", 		array(), "непогасен" );			

				if ( isset($_SESSION['invoice'][$nIDUser]['sale_doc']) ) {
					$deliver = $_SESSION['invoice'][$nIDUser]['sale_doc'];
					
					$_SESSION['invoice'][$nIDUser]['sale_doc']['doc_num'] 			= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['doc_date'] 			= time();
					$_SESSION['invoice'][$nIDUser]['sale_doc']['doc_type'] 			= "faktura";
					$_SESSION['invoice'][$nIDUser]['sale_doc']['doc_status'] 		= "final";
					$_SESSION['invoice'][$nIDUser]['sale_doc']['id_credit_master'] 	= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['id_schet'] 			= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['total_sum'] 		= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['orders_sum'] 		= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['last_order_id'] 	= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['last_order_time'] 	= "0000-00-00 00:00:00";
					$_SESSION['invoice'][$nIDUser]['sale_doc']['id_bank_account'] 	= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['is_auto'] 			= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['note'] 				= "";
					$_SESSION['invoice'][$nIDUser]['sale_doc']['gen_pdf'] 			= 0;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['created_user'] 		= $nIDUser;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['created_time'] 		= time();
					$_SESSION['invoice'][$nIDUser]['sale_doc']['to_arc'] 			= 0;
					
					
					$d_name 	= isset($deliver['deliverer_name']) 	? $deliver['deliverer_name'] 		: "";
					$d_address 	= isset($deliver['deliverer_address']) 	? $deliver['deliverer_address']		: "";
					$d_ein		= isset($deliver['deliverer_ein']) 		? $deliver['deliverer_ein'] 		: "";
					$d_ein_dds	= isset($deliver['deliverer_ein_dds'])	? $deliver['deliverer_ein_dds'] 	: "";
					$d_mol 		= isset($deliver['deliverer_mol']) 		? $deliver['deliverer_mol'] 		: "";
										
					$oResponse->setFormElement('form1', 'deliverer_name',		array(), $d_name );
					$oResponse->setFormElement('form1', 'deliverer_address',	array(), $d_address );
					$oResponse->setFormElement('form1', 'deliverer_ein',		array(), $d_ein );
					$oResponse->setFormElement('form1', 'deliverer_ein_dds',	array(), $d_ein_dds );
					$oResponse->setFormElement('form1', 'deliverer_mol',		array(), $d_mol );				
				}
				
				if ( isset($_SESSION['invoice'][$nIDUser]['sale_doc']['id_client']) && !empty($_SESSION['invoice'][$nIDUser]['sale_doc']['id_client']) ) {
					$nIDClient 	= $_SESSION['invoice'][$nIDUser]['sale_doc']['id_client'];
					$aClient 	= $oDBClients->getRecord( $nIDClient );
					
					$cl_name 	= isset($aClient['name']) 			? $aClient['name'] 				: "";
					$cl_address = isset($aClient['address']) 		? $aClient['address']			: "";
					$cl_ein		= isset($aClient['invoice_ein']) 	? $aClient['invoice_ein'] 		: "";
					$cl_ein_dds	= isset($aClient['invoice_ein_dds'])? $aClient['invoice_ein_dds'] 	: "";
					$cl_mol 	= isset($aClient['invoice_mol']) 	? $aClient['invoice_mol'] 		: "";
					
					$_SESSION['invoice'][$nIDUser]['sale_doc']['client_name'] 		= $cl_name;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['client_address'] 	= $cl_address;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['client_ein'] 		= $cl_ein;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['client_ein_dds'] 	= $cl_ein_dds;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['client_mol'] 		= $cl_mol;
					
					$oResponse->setFormElement('form1', 'client_name',		array(), $cl_name );
					$oResponse->setFormElement('form1', 'client_address',	array(), $cl_address );
					$oResponse->setFormElement('form1', 'client_ein',		array(), $cl_ein );
					$oResponse->setFormElement('form1', 'client_ein_dds',	array(), $cl_ein_dds );
					$oResponse->setFormElement('form1', 'client_mol',		array(), $cl_mol );	
					$oResponse->setFormElement('form1', 'id_client',		array(), $nIDClient );						
					//APILog::Log(0, $aPeson);
					
					$sViewType 		= !empty($aClient['invoice_layout']) 	? $aClient['invoice_layout'] 	: "single";
					$sPaidType 		= !empty($aClient['invoice_payment']) 	? $aClient['invoice_payment'] 	: "cash";
					$sClRecipient 	= !empty($aClient['invoice_recipient']) ? $aClient['invoice_recipient'] : "";
					
					$_SESSION['invoice'][$nIDUser]['sale_doc']['paid_type'] = $sPaidType;
					$_SESSION['invoice'][$nIDUser]['sale_doc']['view_type'] = $sViewType;
	
					$oResponse->setFormElement( "form1", "client_recipient", 	array(), $sClRecipient );
				}
				
				$oResponse->setFormElement( "form1", "view_type", array("value" => $sViewType) );
				$oResponse->setFormElement( "form1", "paid_type", array("value" => $sPaidType) );	
				
				$oDBSalesDocsRows->getReport3( $oResponse, $sViewType );			
				
				// Павел
				if ( isset($_SESSION['invoice'][$nIDUser]['rows']) ) {
					$oFirm	= new DBFirms();
					$aRows 	= $_SESSION['invoice'][$nIDUser]['rows'];
					$office = 0;
					$sum 	= 0;
					$aBanks = array();
					
					foreach ( $aRows as $value ) {
						if ( $sum < $value['total_sum'] ) {
							$sum 	= $value['total_sum'];
							$office = $value['id_office'];
						}
					}
					
					$nFirm 	= $oFirm->getFirmByOffice( $office );
					$aBanks = $oDBSalesDocs->getBankByFirm( $nFirm );
					//APILog::Log(0, $aBanks);
					foreach ( $aBanks as $val ) {
						$oResponse->setFormElementChild( 'form1', 'bank_acc', array('value' => $val['id']), $val['name_account'] );
					}					
				}
							
			}

			$oResponse->printResponse("Документ за продажба",'sale_doc');
		}
		
		public function change_view(DBResponse $oResponse) {
			// Pavel
			$nID 		= Params::get("nID", 0);
			$sViewType 	= Params::get("view_type", "single");
			
			$oDBSalesDocsRows = new DBSalesDocsRows();
			
			if ( !empty($nID) ) {
				$oDBSalesDocsRows->getReport2($oResponse, $nID, $sViewType);
			} else {
				$oDBSalesDocsRows->getReport3($oResponse, $sViewType);
			}
			
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
			//$oSyncMoney 		= new DBSyncMoney();
			
			/* Анулирвам документа за продажба */
			
			$aSaleDoc = array();
			$aSaleDoc['id'] = $nID;
			$aSaleDoc['doc_status'] = 'canceled';
			
			$oDBSalesDocs->update($aSaleDoc);
			
			$oDBSalesDocs->getRecord($nID, $aSaleDoc);
			
//			$nIDSchet = isset($aSaleDoc['id_schet']) ? $aSaleDoc['id_schet'] : 0;
//			
//			if ( !empty($nIDSchet) ) {
//				$oSyncMoney->invalidate( $nIDSchet );
//			}
			
		
			if($aSaleDoc['doc_type'] != "kreditno izvestie" && $aSaleDoc['doc_type'] != "debitno izvestie") {
			
				/* Връщам старите падежи на услугите по обектите */
					
				$aRowsFirstMonthsPaid = $oDBSalesDocsRows->getFirstPaidMonths($nID);
				$oDBSalesDocs->returnOldObjectLastPaids($aRowsFirstMonthsPaid);
				//APILog::Log(0, $aRowsFirstMonthsPaid);
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
		
		
		public function save2(DBResponse $oResponse) {
			global $db_sod, $db_finance, $db_system;
			// Pavel
			
			$nID 				= Params::get('nID', 0);
			$sDocStatus 		= Params::get('sDocStatus', '');
			$nIDClient 			= Params::get('id_client', 0);
			$sClientName 		= Params::get('client_name', '');
			$sClientAddress 	= Params::get('client_address', '');
			$sClientEin			= Params::get('client_ein', '');
			$sClientEinDds 		= Params::get('client_ein_dds', '');
			$sClientMol 		= Params::get('client_mol', '');
			$sDelivererName 	= Params::get('deliverer_name', '');
			$sDelivererAddress 	= Params::get('deliverer_address', '');
			$sDelivererEin 		= Params::get('deliverer_ein', '');
			$sDelivererEinDds 	= Params::get('deliverer_ein_dds', '');
			$sDelivererMol 		= Params::get('deliverer_mol', '');
			$sViewType 			= Params::get('view_type', 'single');
			$sDocDate 			= Params::get('sDocDate', '');
			$sPaidType 			= Params::get('paid_type', 'cash');
			$nBankAcc 			= Params::get('bank_acc', 0);
			$sClientRecipient 	= Params::get('client_recipient', '');
			$sNote 				= Params::get('note', '');
			$sConfirmClick		= Params::get('confirm_click', 'false');
			$nIDUser 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$nIDAccount 		= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account'] : 0;  // PowerSched
			
			if ( !in_array($sViewType, array("single", "detail", "by_objects", "by_services")) ) {
				$sViewType = 'single';
			}
			
			$sDocDate 	= !empty($sDocDate) ? jsDateToTimestamp($sDocDate) : "0000-00-00";
			$sPaidType 	= in_array( $sPaidType, array("cash", "bank") ) ? $sPaidType : "cash";
			
			$_SESSION['invoice'][$nIDUser]['sale_doc']['paid_type'] = $sPaidType;
			$_SESSION['invoice'][$nIDUser]['sale_doc']['view_type'] = $sViewType;
			
			if ( strtotime("-8 days") > $sDocDate ) {
				throw new Exception("Датата на документа трябва да е най-много -7 дена от текущата дата");
			}
			
			$oDBSalesDocs 		= new DBSalesDocs();		
			$oDBSalesDocsRows 	= new DBSalesDocsRows();	
			$oDBClientsObjects 	= new DBClientsObjects();
			$oDBClients 		= new DBClients();
			$oDBSystem 			= new DBSystem();
			$oSyncMoney 		= new DBSyncMoney(); 
			$oDBObjectServices 	= new DBObjectServices();
			$oDBObjectsSingles 	= new DBObjectsSingles();
			
			$aData 				= array();
			$aClient 			= array();
				
			if ( empty($sClientName) ) {
				throw new Exception("Въведете име на клиента");
			}
				
			if ( empty($sClientAddress) ) {
				throw new Exception("Въведете адрес на клиента");
			}
				
			if ( empty($sClientEin) ) {
				throw new Exception("Въведете ЕИН на клиента");
			}
				
			if ( empty($sClientMol) ) {
				throw new Exception("Въведете МОЛ на клиента");
			}
			
			if ( empty($sClientRecipient) ) {
				$sClientRecipient = $sClientMol;
			}			
				
			if ( empty($sDelivererName) ) {
				throw new Exception("Въведете име на доставчика");
			}
				
			if ( empty($sDelivererAddress) ) {
				throw new Exception("Въведете адрес на доставчика");
			}
				
			if ( empty($sDelivererEin) ) {
				throw new Exception("Въведете ЕИН на доставчика");
			}
				
			if ( empty($sDelivererMol) ) {
				throw new Exception("Въведете МОЛ на доставчика");
			}
						
//			if ( !empty($nIDClient) ) {
//				$aClient = $oDBClients->getRecord( $nIDClient );
//			} else {
//				$aClient = $oDBClients->getClientByEIN( $sClientEin );
//			}
				
			$aClient = $oDBClients->getClientByEIN( $sClientEin );
			
			//APILog::Log(0, $aClient);
			$aClient['id'] 				= $nIDClient;
			$aClient['name'] 			= $sClientName;
			$aClient['invoice_address'] = $sClientAddress;
			$aClient['invoice_ein'] 	= $sClientEin;
			$aClient['invoice_ein_dds'] = $sClientEinDds;
			$aClient['invoice_mol'] 	= $sClientMol;
			
			if ( empty($nIDClient) ) {
				$aClient['address'] 			= $sClientAddress;
				$aClient['invoice_recipient'] 	= $sClientRecipient;
				$aClient['invoice_layout'] 		= $sViewType;
				$aClient['invoice_payment'] 	= $sPaidType;
			}
				
			$oDBClients->update( $aClient );
			
			$nIDClient 	= $aClient['id'];
			
			if ( !empty($nIDClient) ) {
				$aRows	= $_SESSION['invoice'][$nIDUser]['rows'];
				
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_name'] 		= $sClientName;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_ein'] 		= $sClientEin;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_ein_dds'] 	= $sClientEinDds;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_address'] 	= $sClientAddress;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_mol'] 		= $sClientMol;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['client_recipient'] 	= $sClientRecipient;		
				
				foreach ( $aRows as $aItems ) {
					$aDataClient = array();
					$aDataClient['id_client']		= $nIDClient;
					$aDataClient['id_object']		= $aItems['id_object'];
					$aDataClient['attach_date']		= time();
					$aDataClient['updated_user']	= $nIDUser;
					$aDataClient['updated_time']	= time();
					$aDataClient['to_arc']			= 0;

					$oDBClients->updateClientObject($aDataClient);					
				}
			}
			
			$_SESSION['invoice'][$nIDUser]['sale_doc']['id_client'] 		= $nIDClient;
			$_SESSION['invoice'][$nIDUser]['sale_doc']['id_bank_account'] 	= $nBankAcc;
			
			if ( empty($nID) ) {
				// Създаваме нов документ
				$db_finance->StartTrans();
				$db_system->StartTrans();
				$db_sod->StartTrans();
				
				$aSystem = $oDBSystem->getRow();
				$nLastNumSaleDoc = isset( $aSystem['last_num_sale_doc'] ) ? $aSystem['last_num_sale_doc'] : 0;				
				
				$aSaleDoc 	= array();
				$aRows 		= array();
				$aSaleDoc 	= $_SESSION['invoice'][$nIDUser]['sale_doc'];
				$aRows		= $_SESSION['invoice'][$nIDUser]['rows'];
				
				$aSaleDoc['doc_num'] = $nLastNumSaleDoc + 1;
				//APILog::Log(0, $aSaleDoc);
				//throw new Exception("ws");
				$oDBSalesDocs->update($aSaleDoc);
				$nID 			= $aSaleDoc['id'];
				$aSaleDocSchet 	= array();
				$aService		= array();
				$sPadej			= "";
				$id_office		= 0;
				$sService		= "";
				$id_schet		= 0;
				
				foreach ( $aRows as $val ) {
					$val['id_sale_doc'] = $nID;
					
					$oDBSalesDocsRows->update( $val );
					
					if ( isset($val['is_dds']) && !empty($val['is_dds']) ) {
						continue;	
					}
										
					$nIDService = isset($val['nIDService']) ? $val['nIDService'] : 0;
					
					if ( $val['sTypeService'] == "month" ) {
						$aService = $oDBObjectServices->getService( $nIDService );
						
						list( $y, $m, $d ) = explode( "-", $val['month'] );
						
						if ( empty($sPadej) ) {
							$sPadej = $y."-".$m."-01";
						} else {
							if ( $sPadej < $y."-".$m."-01" ) {
								$sPadej = $y."-".$m."-01";
							}
						}
						
						$aObjectService 			= array();
						$aObjectService['id'] 		= $nIDService;
						$aObjectService['last_paid'] = $y."-".$m."-01";		
			
						$oDBObjectServices->update($aObjectService);		//vdigam padeja na uslugata		
						
						//PowerSchet
						$aSaleDocSchet['type'] = "month";
						
						if ( !empty($nIDAccount) ) {
							$aPadej = array();
							$aPadej['id_obj'] 		= $aService['id_oldobj'];
							$aPadej['paid_month']	= $y."-".$m."-01";
							
							$oSyncMoney->setPaidMonth( $aPadej );	
						}
																
					} else {
						$aService = $oDBObjectsSingles->getSingle( $nIDService );
						
						$aObjectSingle 				= array();
						$aObjectSingle['id'] 		= $nIDService;
						$aObjectSingle['paid_date'] = time();
						$aObjectSingle['id_sale_doc'] = $nID;
						
						$oDBObjectsSingles->update($aObjectSingle);	
						
						//PowerSchet
						$aSaleDocSchet['type'] = "single";					
					}
					
					//isset($aSaleDocSchet['sum']) ? $aSaleDocSchet['sum'] += $val['total_sum'] : $aSaleDocSchet['sum'] = $val['total_sum'];
					
					if ( $aSaleDocSchet['type'] == "month" ) {
						isset($aSaleDocSchet['id_objs']) ? $aSaleDocSchet['id_objs'] 	.= ",".$aService['id_oldobj'] : $aSaleDocSchet['id_objs'] = $aService['id_oldobj'];
						isset($aSaleDocSchet['taxes']) 	? $aSaleDocSchet['taxes'] 	+= 1 : $aSaleDocSchet['taxes'] = 1;
							
						if ( !isset($aSaleDocSchet['paid_month']) || ($aSaleDocSchet['paid_month'] < $y."-".$m."-01") ) {
							$aSaleDocSchet['paid_month'] = $sPadej;
						}
					} else {
						$findSingle = $oSyncMoney->findSingle( $aService['id_oldobj'], $aService['total_sum'] );
							
						if ( empty($findSingle) ) {
							// Записа не съществува, добавяме го
								
							$aSaleDocSchetSingle['id_obj']			= $aService['id_oldobj'];
							$aSaleDocSchetSingle['data']			= date("Y-m-d");
							$aSaleDocSchetSingle['info']			= iconv( 'UTF-8', 'CP1251', $aService['service_name']." [ТНЕТ]" );
							$aSaleDocSchetSingle['sum']				= $aService['total_sum'];
							$aSaleDocSchetSingle['sum_p']			= 0;
							$aSaleDocSchetSingle['currency']		= "BGL";
							$aSaleDocSchetSingle['singlepay']		= 0;
							$aSaleDocSchetSingle['faktura']			= "faktura";
							$aSaleDocSchetSingle['bank']			= $sPaidType == "bank" ? 1 : 0;
							$aSaleDocSchetSingle['normal']			= 1;
							$aSaleDocSchetSingle['tax_num']			= $aSaleDoc['doc_num'];
							$aSaleDocSchetSingle['confirm']			= 0;
							$aSaleDocSchetSingle['confirm_date']	= "0000-00-00 00:00:00";
							$aSaleDocSchetSingle['faktura_type']	= "faktura";
							$aSaleDocSchetSingle['f_name']			= iconv( 'UTF-8', 'CP1251', $aClient['name'] );
							$aSaleDocSchetSingle['f_address']		= iconv( 'UTF-8', 'CP1251', $aClient['invoice_address'] );
							$aSaleDocSchetSingle['f_dn']			= iconv( 'UTF-8', 'CP1251', $aClient['invoice_ein'] );
							$aSaleDocSchetSingle['f_bulstat']		= iconv( 'UTF-8', 'CP1251', $aClient['invoice_ein_dds'] );
							$aSaleDocSchetSingle['f_mol']			= iconv( 'UTF-8', 'CP1251', $aClient['invoice_mol'] );
							$aSaleDocSchetSingle['p_name']			= iconv( 'UTF-8', 'CP1251', $aClient['invoice_recipient'] );
							$aSaleDocSchetSingle['p_lk']			= "";
							$aSaleDocSchetSingle['p_year']			= "";
							$aSaleDocSchetSingle['p_num']			= "";
							$aSaleDocSchetSingle['measure']			= iconv( 'UTF-8', 'CP1251', "бр." );
							$aSaleDocSchetSingle['br']				= 1;
							$aSaleDocSchetSingle['id_document']		= $aSaleDoc['doc_num'];						
							//APILog::Log(0, $aSaleDocSchetSingle);
							if ( !empty($nIDAccount) ) {
								$oSyncMoney->updateSchetSingles( $aSaleDocSchetSingle );
							}
						}							
					}					

				}
				
				$id_office 	= isset($aService['id_office']) ? $aService['id_office'] : 0;
				$sService 	= iconv( 'UTF-8', 'CP1251', $aService['service_name']." [ТНЕТ]" );
				$id_schet 	= isset($aService['id_schet']) ? $aService['id_schet'] : 0;				
						
				//APILog::Log(0, $aService);
				//throw new Exception($aService['service_name']);
				
				$nIDOffice = $oSyncMoney->getDirectionByOffice( $id_office );

				$aSaleDocSchet['data']			= date("Y-m-d");
				$aSaleDocSchet['mataksa']		= 0;
				$aSaleDocSchet['bank']			= $sPaidType == "bank" ? 1 : 0;
				$aSaleDocSchet['confirm']		= 0;
				$aSaleDocSchet['confirm_date']	= "0000-00-00 00:00:00";
				$aSaleDocSchet['normal']		= 0;
				$aSaleDocSchet['tax_num']		= $aSaleDoc['doc_num'];
				$aSaleDocSchet['faktura_type']	= 1;
				$aSaleDocSchet['f_name']		= iconv( 'UTF-8', 'CP1251', $aClient['name'] );
				$aSaleDocSchet['f_address']		= iconv( 'UTF-8', 'CP1251', $aClient['invoice_address'] );
				$aSaleDocSchet['f_dn']			= iconv( 'UTF-8', 'CP1251', $aClient['invoice_ein'] );
				$aSaleDocSchet['f_bulstat']		= iconv( 'UTF-8', 'CP1251', $aClient['invoice_ein_dds'] );
				$aSaleDocSchet['f_mol']			= iconv( 'UTF-8', 'CP1251', $aClient['invoice_mol'] );
				$aSaleDocSchet['p_name']		= iconv( 'UTF-8', 'CP1251', $aClient['invoice_recipient'] );
				$aSaleDocSchet['p_lk']			= "";
				$aSaleDocSchet['p_year']		= "";
				$aSaleDocSchet['p_num']			= "";
				$aSaleDocSchet['measure']		= iconv( 'UTF-8', 'CP1251', "бр." );
				$aSaleDocSchet['br']			= 1;
				$aSaleDocSchet['zero']			= 0;
				$aSaleDocSchet['zero_date']		= "0000-00-00";
				$aSaleDocSchet['faktura']		= 1;
				$aSaleDocSchet['valid_sum']		= 0;
				$aSaleDocSchet['smetka_id']		= $nIDAccount;  // Da se promeni na smetkata, koqto shte poluchava sumi prez Telenet
				$aSaleDocSchet['direction_id']	= $nIDOffice;   // Relacia!!!
				$aSaleDocSchet['typepay_id']	= $id_schet;
				$aSaleDocSchet['info']			= $sService;
				$aSaleDocSchet['single_pay']	= 0; 
				$aSaleDocSchet['user_id']		= 17;  // ID-to na Nadia 
				$aSaleDocSchet['nareditel']		= ""; 
				$aSaleDocSchet['poluchatel']	= ""; 
				$aSaleDocSchet['razhod_num']	= ""; 
				$aSaleDocSchet['saldo']			= 0;  // Da vidim kak se smqta tova 
				$aSaleDocSchet['sum']			= $aSaleDoc['total_sum'];
				// Обработка на месечни таблици
				if ( isset($aSaleDocSchet['id_objs']) && !empty($aSaleDocSchet['id_objs']) ) {
					$nIDObject = $oSyncMoney->getMasterFromObjs( $aSaleDocSchet['id_objs'] );			
					
					$aSaleDocSchet['id_obj'] 	= $nIDObject;
					
					if ( !empty($nIDAccount) ) {
						$nIDDocument = $oSyncMoney->updateSchetMonth( $aSaleDocSchet );
					}
				}
	
				if ( !empty($nIDDocument) && !empty($aSaleDoc['id']) ) {
					$aData 				= array();
					$aData['id'] 		= $aSaleDoc['id'];
					$aData['id_schet'] 	= date("Ym").zero_padding($nIDDocument, 7);
						
					if ( !empty($nIDAccount) ) {	
						$oDBSalesDocs->update( $aData ); 
					}
				}					
				
				//APILog::Log(0, $aSaleDocSchet);		
								
				$oDBSystem->setLastNumSaleDoc( $nLastNumSaleDoc + 1 );		
				$oResponse->setFormElement( "form1", "nID", array(), $nID );

				//print(
				//	"<script>
				//		requestWin = window.open('".$host."');
				//	</script>"
				//);

				if ( empty($nID) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();	
					$db_sod->FailTrans();				
				} else {							
					$db_finance->CompleteTrans();
					$db_system->CompleteTrans();
					$db_sod->CompleteTrans();
				}
				
				$host = "http://".$_SERVER['SERVER_NAME']."/telenet/page.php?page=sale_doc_info2";
				$oResponse->setFormElement( "form1", "url", array(), $host );	
				$oResponse->printResponse();
				exit();			
			}
			

			
			//throw new Exception( $nID );
			
			$oDBSalesDocs->getRecord( $nID, $aData );
			$aObjectIDs = $oDBSalesDocsRows->getObjectsIDs( $nID );
				
			foreach ($aObjectIDs as $value) {
				$nIDClientTmp = $oDBClientsObjects->getIDClientByIDObject($value['id_object']);
					
				if ( empty($nIDClientTmp) ) {
					$aClientObject = array();
					$aClientObject['id_client'] 	= $nIDClient;
					$aClientObject['id_object'] 	= $value['id_object'];
					$aClientObject['attach_date'] 	= time();
						
					$oDBClientsObjects->update( $aClientObject );
				}		
			}
			
			$aData['doc_status'] 		= "final";
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
			
			if ( !empty($nIDClient) ) {
				$aData['id_client'] 	= $nIDClient;
			}
			//APILog::Log(0, $aData);
			$oDBSalesDocs->update($aData);
			
			// Schet
			$nIDSchet 	= isset($aData['id_schet']) ? $aData['id_schet'] : 0;
			$sType 		= isset($aData['doc_type']) ? $aData['doc_type'] : "faktura";
			$sPaid 		= isset($aData['paid_type']) ? $aData['paid_type'] : "cash";
							
			switch ( $sType ) {
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
			
			$oResponse->setFormElement( "form1", "open_order", array(), "true" );
			$oResponse->setFormElement( "form1", "sDocStatus", array(), "final" );
			$oResponse->printResponse();
			
		}		
	}

?>