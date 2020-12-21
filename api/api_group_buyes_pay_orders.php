<?php
	class ApiGroupBuyesPayOrders {
		
		public function load(DBResponse $oResponse) {
			
			$sIDs  		= Params::get("sIDs", "");
			$sBank 		= Params::get("sBank", "");
			$account	= Params::get("account", 0);
			
			$sBank		= !empty($account) ? $account : $sBank;
			
			$oDBBuyDocs 		= new DBBuyDocs();
			$oDBBankAccounts 	= new DBBankAccounts();
			$aIDBuyDocs 		= explode( ",", $sIDs );
			$nSum 				= 0;

			foreach ($aIDBuyDocs as $value) {
				if ( empty($value) ) {
					continue;
				}
								
				$aBuyDoc = array();
				$oDBBuyDocs->getRecord($value, $aBuyDoc);
				
				$total = isset($aBuyDoc['total_sum']) 	? $aBuyDoc['total_sum']  : 0;
				$order = isset($aBuyDoc['orders_sum']) 	? $aBuyDoc['orders_sum'] : 0;
				$nSum += $total - $order;
			}
			
			$aBankAccounts 	= array();
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$aBankAccounts 	= $oDBBankAccounts->getByPersonForOperate2( $nIDPerson );
			
			$oResponse->setFormElement( 'form1', 'account' );
			
			foreach ($aBankAccounts as $key => $value) {
				if ( $key == $sBank ) {
					$oResponse->setFormElementChild( 'form1', 'account', array("value" => $key, "id" => $value['type'], "selected" => "selected"), $value['name_account'] );
					$oResponse->setFormElement( 'form1', 'sTypeAccount', array(), $value['type'] );
					$sTypeAcc	= Params::set("sTypeAccount", $value['type']);					
				} else {
					$oResponse->setFormElementChild( 'form1', 'account', array("value" => $key, "id" => $value['type']), $value['name_account'] );
				}
			}
			
			$nSum = sprintf( "%0.2f", $nSum );
			
			$oResponse->setFormElement( 'form1', 'sum', array(), $nSum );
			
			$this->check($oResponse);
			
			$oResponse->printResponse();
			
		}
		
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}		
		
		public function check( DBResponse $oResponse ) {
			global $db_name_finance, $db_finance;
			
			$oBuyDocs 	= new DBBuyDocs();
			
			$sIDs  		= Params::get("sIDs", "");
			$sTypeAcc	= Params::get("sTypeAccount", "cash");	
			
			$aIDBuyDocs 	= explode(",", $sIDs);
			$sAccountType 	= $sTypeAcc == "cash" ? "cash" : "bank";
			//$oResponse->setAlert($sTypeAcc);
			$flag			= "";
			$aBuyDoc		= array();
	
			foreach ( $aIDBuyDocs as $nID ) {
				$oBuyDocs->getRecord($nID, $aBuyDoc);
				$sPaid 	= isset($aBuyDoc['paid_type']) 	? $aBuyDoc['paid_type'] : "none";
				$nNum	= isset($aBuyDoc['doc_num']) 	? $aBuyDoc['doc_num'] 	: 0;
				
				if ( $sPaid != $sAccountType ) {
					$flag .= !empty($flag) ? ",".$nNum : $nNum;
				}
			}
			
			if ( !empty($flag) ) {
				$oResponse->setFormElement("form1", "flag", array(), $flag);
			} else {
				$oResponse->setFormElement("form1", "flag", array(), "-1");
			}
		}
				
		public function confirm( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance, $db_system, $db_name_system, $db_name_sod;
			
			$sIDs  		= Params::get("sIDs", "");
			$account	= Params::get("account", "");
			$nSum		= Params::get("sum", 0);
			$sTypeAcc	= Params::get("sTypeAccount", "cash");
			
			$aIDBuyDocs 	= explode(",", $sIDs);
			$sAccountType 	= $sTypeAcc == "cash" ? "cash" : "bank";
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$nSum 			= floatval($nSum);
			$nSum 			= round($nSum, 2);
			$nPaySum		= $nSum;
			
			if ( empty($nSum) ) {
				throw new Exception("Няма непогасени документи в групажа!");
			}
			
			$oDBBuyDocs 		= new DBBuyDocs();
			$oDBBuyDocsRows 	= new DBBuyDocsRows();
			$oDBOrders 			= new DBOrders();
			$oDBOrdersRow 		= new DBOrdersRows();
			$oDBSystem 			= new DBSystem();
			$oDBAccountStates 	= new DBAccountStates();
			$oSyncMoney 		= new DBSyncMoney();
			$oServices			= new DBObjectServices();
			$oFirm				= new DBFirms();
			$oOffices			= new DBOffices();
			$oSaldo				= new DBSaldo();			
			
			$nBuyDocsSum 		= 0;
			$tmpSum				= 0;
			$aDocsRows			= array();
			$nIDransfer			= 0;
			$nIDDDS				= 0;
			$nTotalSum 			= 0;
			 
			// Валидация на операцията!
			foreach ( $aIDBuyDocs as $key => $nID ) {
				if ( empty($nID) ) {
					continue;
				}
				
				$nIDransfer = $oDBBuyDocsRows->checkForTransfer($nID);

				if ( !empty($nIDransfer) ) {
					throw new Exception("В групажа участват документи с направление по ТРАНСФЕР!!!");
				}
				
				$nIDDDS		= $oDBBuyDocsRows->checkForDDS($nID);
				
				if ( !empty($nIDDDS) ) {
					throw new Exception("В групажа участват документи с направление по ДДС!!!");
				}

				$nTotalSum = $oDBBuyDocsRows->getDuty($nID);
				
				if ( sprintf("%01.2f", abs($nTotalSum)) == "0.00" ) {
					unset($aIDBuyDocs[$key]);
				}				
			}			
			
			foreach ( $aIDBuyDocs as $value ) {
				// Pavel
				if ( empty($value) ) {
					continue;
				}
				
				$tmpSum			= 0;
				$aBuyDoc 		= array();
				$aBuyRows		= array();
				$oDBBuyDocs->getRecord($value, $aBuyDoc);
				
				$total 	= isset($aBuyDoc['total_sum']) 	? $aBuyDoc['total_sum']  : 0;
				$order 	= isset($aBuyDoc['orders_sum']) ? $aBuyDoc['orders_sum'] : 0;
				$tmpSum = $total - $order; 				
				
				//$tmpSum 		= isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;
				$nBuyDocsSum 	+= $tmpSum;
				
				$sID 	= isset($aBuyDoc['id_schet']) 	? $aBuyDoc['id_schet'] 	: 0;
				$sType 	= isset($aBuyDoc['doc_type']) 	? $aBuyDoc['doc_type'] 	: "kvitanciq";
				$sPaid 	= isset($aBuyDoc['paid_type']) 	? $aBuyDoc['paid_type'] : "cash";
				
				switch ($sType) {
					case "kvitanciq": $nIDType = 0;
					break;
					
					case "oprostena": $nIDType = 0;
					break;	
										
					case "faktura": $nIDType = 1;
					break;
					
					case "kreditno izvestie": $nIDType = 4;
					break;

					case "debitno izvestie": $nIDType = 3;
					break;
					
					default: $nIDType = 0;
					break;					
				}
				
				$aSchetRows = array();
				$aSchetRows = $oDBBuyDocsRows->getSchetByIDDoc($value);

				foreach ( $aSchetRows as $aSchetItems ) {
					//sleep(1);
					$nIDSchet 	= isset($aSchetItems['id_schet_row']) 	? $aSchetItems['id_schet_row'] 	: 0;
					$nIDTnet	= isset($aSchetItems['id']) 			? $aSchetItems['id'] 			: 0;	
					
					if ( $this->isValidID($nIDSchet) && $this->isValidID($nIDTnet) ) {
						
						// Проверка за съвместимост
						$table1 = "mp".substr($nIDSchet, 0, 6);
						$table2 = "mp".date("Ym");
						$status	= true;
								
						if ( $table1 != $table2 ) {
							$nLast = $oSyncMoney->moveDocument($nIDSchet);
									
							if ( !empty($nLast) ) {
								$nIDSchet				= date("Ym").zero_padding($nLast, 7);
										
								$aUpdate 				= array();
								$aUpdate['id'] 			= $nIDTnet;
								$aUpdate['id_schet_row']= $nIDSchet;
										
								$oDBBuyDocsRows->update($aUpdate);
							}
						}
						
						// Месечни задължения/ДДС								
						$aRows 				= array();
						$aRows['id'] 		= $nIDSchet;
						$aRows['sum'] 		= "999999999";
						$aRows['smetka']	= $account;

						$status = $oSyncMoney->payMonth($aRows);																	
					}							
				}				
			}
			
			unset($value);
			
			$nBuyDocsSum 	= floatval($nBuyDocsSum);
			$nBuyDocsSum 	= round($nBuyDocsSum, 2);
			
			$db_finance->StartTrans();
			$db_system->StartTrans();
			
			try {				
				foreach ($aIDBuyDocs as $nID) {
					if ( empty($nID) ) {
						continue;
					}
									
					sleep(1);
					
					$aBuyRows	= array();
					$aBuyDoc 	= array();
					$nTotalSum	= 0;
					$sBuyName	= PREFIX_BUY_DOCS.substr($nID, 0, 6);
					$sRowsName	= PREFIX_BUY_DOCS_ROWS.substr($nID, 0, 6);
					$nIDOrder	= 0;
					$nAccState	= 0;

					// НАЧАЛНА наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$account} LIMIT 1");
					$nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;						

					// Следващ номер за ордер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;

					$nTotalSum = $oDBBuyDocsRows->getDuty($nID);
					$oDBBuyDocs->getRecord($nID, $aBuyDoc);
					
					if ( sprintf("%01.2f", abs($nTotalSum)) == "0.00" ) {
						continue;
					}	
					
					$nTotalSum 	= sprintf("%01.2f", ($nTotalSum));
					$nIDDoc		= isset($aBuyDoc['id']) ? $aBuyDoc['id'] : 0;		
					
					if ( $nTotalSum > 0 ) {
						$sType = "expense";
					} else {
						$sType = "earning";
					}					
					
					$aDataOrder						= array();
					$aDataOrder['id']				= 0;
					$aDataOrder['num']				= $nLastOrder;
					$aDataOrder['order_type'] 		= $sType;
					$aDataOrder['id_transfer']		= 0;
					$aDataOrder['order_date']		= time();
					$aDataOrder['order_sum']		= abs($nTotalSum);	
					$aDataOrder['account_type']		= $sAccountType;
					$aDataOrder['id_person']		= $nIDPerson;
					$aDataOrder['account_sum']		= $nAccState - $nTotalSum;
					$aDataOrder['bank_account_id']	= $account;
					$aDataOrder['doc_id']			= $nID;
					$aDataOrder['doc_type']			= "buy";
					$aDataOrder['note']				= "Групово валидиране!";
					$aDataOrder['created_user']		= $nIDPerson;
					$aDataOrder['created_time']		= time();
					$aDataOrder['updated_user']		= $nIDPerson;
					$aDataOrder['updated_time']		= time();

					$oDBOrders->update($aDataOrder);
					
					$nIDOrder 	= $aDataOrder['id'];	
					
					// Обновяваме следващ номер за ордер
					$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");

					// Обновяваме тотала в документа
					$db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyName} SET orders_sum = orders_sum + '{$nTotalSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW(), updated_user = {$nIDPerson}, updated_time = NOW() WHERE id = '{$nID}'");
										
					// Разбивка
					if ( !empty($nIDOrder) ) {
						$aBuyRows = $oDBBuyDocsRows->getByIDBuyDoc($nID);
						
						foreach ( $aBuyRows as $val ) {
							if ( $val['total_sum'] != $val['paid_sum'] ) {
								$real_sum	= $val['total_sum'] - $val['paid_sum'];
								$aFirm 		= $oFirm->getFirmByIDOffice($val['id_office']);
								$nIDOffice	= 0;
								$nIDFirm	= 0;
								$sFirm		= "";
								$is_dds		= 0;
								$nIDRow		= isset($val['id']) 			? $val['id'] 			: 0;
								$nIDDirect	= isset($val['id_direction']) 	? $val['id_direction'] 	: 0;
																	
								if ( $val['is_dds'] > 0 ) {
									$nIDOffice 	= isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
									$nIDFirm	= $oFirm->getFirmByOffice($nIDOffice);
									$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);								
									$is_dds 	= 1;							
								} else {
									$nIDFirm 	= isset($aFirm['id']) ? $aFirm['id'] : 0;
									$sFirm 		= $oOffices->getFirmNameByIDOffice($val['id_office']);								
								}	

								$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $is_dds);
								$nIDSaldo		= 0;
								$nCurrentSaldo	= 0;
								$nAccountState	= 0;
	
								if ( !empty($aSaldo) ) {
									$nIDSaldo 	= $aSaldo['id'];
								}
								
								// Салдо на фирмата с изчакване!!!
								if ( !empty($nIDSaldo) ) {
									$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
					    			$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
								} else {
									throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
								}	
								
								if ( ($nCurrentSaldo - $real_sum) < 0 ) {
									throw new Exception("Недостатъчно салдо по фирма {$sFirm}!!!", DBAPI_ERR_INVALID_PARAM);
								}		

								// Наличност по сметка
								if ( !empty($account) ) {
									$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$account} LIMIT 1 FOR UPDATE");
									$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
								} else {
									throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
								}			
								
								if ( $nAccountState < $real_sum ) {
									throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
								}								

								$aDataRows								= array();
								$aDataRows['id']						= 0;
								$aDataRows['id_order']					= $nIDOrder;
								$aDataRows['id_doc_row']				= $nIDRow;
								$aDataRows['id_office']					= $val['id_office'];
								$aDataRows['id_object']					= $val['id_object'];
								$aDataRows['id_service']				= 0;
								$aDataRows['id_direction']				= $nIDDirect;
								$aDataRows['id_saldo']					= $nIDSaldo;
								$aDataRows['id_bank']					= $account;
								$aDataRows['saldo_state']				= $nCurrentSaldo - $real_sum;
								$aDataRows['account_state']				= $nAccountState - $real_sum;
								$aDataRows['id_nomenclature_earning']	= 0;
								$aDataRows['id_nomenclature_expense']	= isset($val['id_nomenclature_expense']) ? $val['id_nomenclature_expense'] : 0;
								$aDataRows['month']						= $val['month'];
								$aDataRows['type']						= "free";
								$aDataRows['paid_sum']					= $real_sum;
								$aDataRows['is_dds']					= $is_dds;		

								if ( !empty($real_sum) && (strlen($val['id']) == 13) ) {
									$oDBOrdersRow->update($aDataRows);
									
									// Обновяване на описа на документа
									$db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$real_sum}', paid_date = NOW() WHERE id = '{$nIDRow}' ");
									// Обновяване на салдата на фирмите
									$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$real_sum}' WHERE id = {$nIDSaldo} LIMIT 1");
									
									$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$real_sum}' WHERE id_bank_account = {$account} ");
								}														
							}
						}
					} else {
						$db_finance->FailTrans();
						$db_system->FailTrans();						
					}						
				}
			
				$db_finance->CompleteTrans();	
				$db_system->CompleteTrans();			
			} catch (Exception $e) {
				$db_finance->FailTrans();
				$db_system->FailTrans();
				
				$sMessage = $e->getMessage();
				
				if ( !empty($sMessage) ) {
					$sMessage = "Получена грешка: ".$sMessage;
				} else {
					$sMessage = "Неизвестна грешка!!!";
				}

				throw new Exception($sMessage);
			}
							
			$oResponse->printResponse();
		}
	}

?>