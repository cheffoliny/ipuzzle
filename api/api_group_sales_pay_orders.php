<?php

	class ApiGroupSalesPayOrders {
		
		public function load(DBResponse $oResponse) {
			
			$sIDs  		= Params::get("sIDs", "");
			$sBank 		= Params::get("sBank", "");
			$account	= Params::get("account", 0);
			
			$sBank		= !empty($account) ? $account : $sBank;
			
			$oDBSaleDocs 		= new DBSalesDocs();
			$oDBBankAccounts 	= new DBBankAccounts();
			
			$aIDSaleDocs 		= explode( ",", $sIDs );
			$nSum 				= 0;
			//APILog::Log(0, $aIDSaleDocs);
			foreach ($aIDSaleDocs as $value) {
				if ( empty($value) ) {
					continue;
				}
								
				$aSaleDoc = array();
				$oDBSaleDocs->getRecord($value, $aSaleDoc);
				
				$total = isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;
				$order = isset($aSaleDoc['orders_sum']) ? $aSaleDoc['orders_sum'] : 0;
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
			global $db_name_finance, $db_finance, $db_name_sod;
			
			$oSaleDocs 	= new DBSalesDocs();
			
			$sIDs  		= Params::get("sIDs", "");
			$sTypeAcc	= Params::get("sTypeAccount", "cash");	
			
			$aIDSaleDocs 	= explode(",", $sIDs);
			$sAccountType 	= $sTypeAcc == "cash" ? "cash" : "bank";
			//$oResponse->setAlert($sTypeAcc);
			$flag			= "";
			$aSaleDoc		= array();
	
			foreach ( $aIDSaleDocs as $nID ) {
				$oSaleDocs->getRecord($nID, $aSaleDoc);
				$sPaid 	= isset($aSaleDoc['paid_type']) ? $aSaleDoc['paid_type'] : "none";
				$nNum	= isset($aSaleDoc['doc_num']) 	? $aSaleDoc['doc_num'] 	 : 0;
				
				if ( $sPaid != $sAccountType ) {
					$flag .= !empty($flag) ? ",".$nNum : $nNum;
				}
			}
			
			if ( !empty($flag) ) {
				$oResponse->setFormElement("form1", "flag", array(), $flag);
			} else {
				$oResponse->setFormElement("form1", "flag", array(), "-1");
			}
			
			//$oResponse->printResponse();
		}
		
		public function confirm( DBResponse $oResponse ) {
			global $db_name_finance, $db_finance, $db_system, $db_name_system, $db_name_sod;
			
			$sIDs  		= Params::get("sIDs", "");
			$account	= Params::get("account", "");
			$nSum		= Params::get("sum", 0);
			$sTypeAcc	= Params::get("sTypeAccount", "cash");
			
			$aIDSaleDocs 	= explode(",", $sIDs);
			$sAccountType 	= $sTypeAcc == "cash" ? "cash" : "bank";
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$nSum 			= floatval($nSum);
			$nSum 			= round($nSum, 2);
			$nPaySum		= $nSum;
			
			if ( empty($nSum) ) {
				throw new Exception("Няма непогасени документи в групажа!");
			}
			
			$oDBSaleDocs 		= new DBSalesDocs();
			$oDBSalesDocsRows 	= new DBSalesDocsRows();
			$oDBOrders 			= new DBOrders();
			$oDBOrdersRow 		= new DBOrdersRows();
			$oDBSystem 			= new DBSystem();
			$oDBAccountStates 	= new DBAccountStates();
			$oSyncMoney 		= new DBSyncMoney();
			$oServices			= new DBObjectServices();
			$oFirm				= new DBFirms();
			$oOffices			= new DBOffices();
			$oSaldo				= new DBSaldo();
			
			$nSalesDocsSum 		= 0;
			$tmpSum				= 0;
			$nIDransfer			= 0;
			$nIDDDS				= 0;
			$nTotalSum			= 0;

			// Валидация на операцията!
			foreach ( $aIDSaleDocs as $key => $nID ) {
				if ( empty($nID) ) {
					continue;
				}
				
				$nIDransfer = $oDBSalesDocsRows->checkForTransfer($nID);

				if ( !empty($nIDransfer) ) {
					throw new Exception("В групажа участват документи с направление по ТРАНСФЕР!!!");
				}
				
				$nIDDDS		= $oDBSalesDocsRows->checkForDDS($nID);
				
				if ( !empty($nIDDDS) ) {
					throw new Exception("В групажа участват документи с направление по ДДС!!!");
				}

				$nTotalSum = $oDBSalesDocsRows->getDuty($nID);
				
				if ( sprintf("%01.2f", abs($nTotalSum)) == "0.00" ) {
					unset($aIDSaleDocs[$key]);
				}
			}			
			
			foreach ( $aIDSaleDocs as $value ) {
				// Pavel
				if ( empty($value) ) {
					continue;
				}
				
				$tmpSum			= 0;
				$aSaleDoc 		= array();
				$aSaleRows		= array();
				$oDBSaleDocs->getRecord($value, $aSaleDoc);
				$aSaleRows		= $oDBSalesDocsRows->getByIDSaleDoc($value);
				
				$total 			= isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;
				$order 			= isset($aSaleDoc['orders_sum']) ? $aSaleDoc['orders_sum'] : 0;
				$tmpSum 		= $total - $order; 				
				
				//$tmpSum 		= isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;
				$nSalesDocsSum 	+= $tmpSum;
				
				$sID 	= isset($aSaleDoc['id_schet']) ? $aSaleDoc['id_schet'] : 0;
				$sType 	= isset($aSaleDoc['doc_type']) ? $aSaleDoc['doc_type'] : "kvitanciq";
				$sPaid 	= isset($aSaleDoc['paid_type']) ? $aSaleDoc['paid_type'] : "cash";
				
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
				$aSchetRows = $oDBSalesDocsRows->getSchetByIDDoc($value);

				foreach ( $aSchetRows as $aSchetItems ) {
					
					$nIDSchet 	= isset($aSchetItems['id_schet_row']) 	? $aSchetItems['id_schet_row'] 	: 0;
					$nIDSingle 	= isset($aSchetItems['id_schet']) 		? $aSchetItems['id_schet'] 		: 0;
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
										
								$oDBSalesDocsRows->update($aUpdate);
							}
						}
						
						// Месечни задължения/ДДС								
						$aRows 				= array();
						$aRows['id'] 		= $nIDSchet;
						$aRows['sum'] 		= "999999999";
						$aRows['smetka']	= $account;

						$status = $oSyncMoney->payMonth($aRows);	

						// Еднократни задължения
						if ( !empty($nIDSingle) ) {
							$oSyncMoney->payNow($nIDSingle);
						}																
					}							
				}
			}
			
			unset($value);
			
			$nSalesDocsSum 	= floatval($nSalesDocsSum);
			$nSalesDocsSum 	= round($nSalesDocsSum, 2);
			
			$db_finance->StartTrans();
			$db_system->StartTrans();
			
			try {			
				foreach ($aIDSaleDocs as $nID) {
					if ( empty($nID) ) {
						continue;
					}
					
					sleep(1);

					$aSaleRows		= array();
					$aSaleDoc 		= array();
					$nTotalSum		= 0;
					$sSaleName		= PREFIX_SALES_DOCS.substr($nID, 0, 6);
					$sRowsName		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);
					$nIDOrder		= 0;
					$nAccState		= 0;
						
					// НАЧАЛНА наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$account} LIMIT 1");
					$nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;					

					// Следващ номер за ордер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;

					$nTotalSum = $oDBSalesDocsRows->getDuty($nID);
					$oDBSaleDocs->getRecord($nID, $aSaleDoc);
					
					if ( sprintf("%01.2f", abs($nTotalSum)) == "0.00" ) {
						continue;
					}	
			
					$nTotalSum 	= sprintf("%01.2f", ($nTotalSum));
					$nIDDoc		= isset($aSaleDoc['id']) ? $aSaleDoc['id'] : 0;		
					
					if ( $nTotalSum > 0 ) {
						$sType = "earning";
					} else {
						$sType = "expense";
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
					$aDataOrder['account_sum']		= $nAccState + $nTotalSum;
					$aDataOrder['bank_account_id']	= $account;
					$aDataOrder['doc_id']			= $nID;
					$aDataOrder['doc_type']			= "sale";
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
					$db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nTotalSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW(), updated_user = {$nIDPerson}, updated_time = NOW() WHERE id = '{$nID}'");
					
					// Разбивка
					if ( !empty($nIDOrder) ) {
						$aSaleRows = $oDBSalesDocsRows->getByIDSaleDoc($nID);
						
						foreach ( $aSaleRows as $val ) {
							if ( $val['total_sum'] != $val['paid_sum'] ) {
								$real_sum	= $val['total_sum'] - $val['paid_sum'];
								$aFirm 		= $oFirm->getFirmByIDOffice($val['id_office']);
								$nIDOffice	= 0;
								$nIDFirm	= 0;
								$sFirm		= "";
								$nIDService	= 0;
								$nIDEarning	= 0;	
								$is_dds		= 0;
								$nIDRow		= $val['id'];
								
								$nIDDuty	= isset($val['id_duty_row']) 	? $val['id_duty_row']				: 0;	
								$sMonth		= isset($val['month']) 			? substr($val['month'], 0, 7)."-01"	: "0000-00-00";		
								
								if ( isset($val['type']) && ($val['type'] == "month") && !empty($nIDDuty) ) {
									$aTemp = array();
									$aTemp = $oServices->getServiceByID($nIDDuty);
									
									if ( isset($aTemp['real_paid']) && ($aTemp['real_paid'] < $sMonth) ) {
										$aTempData 				= array();
										$aTempData['id'] 		= $nIDDuty;
										$aTempData['real_paid']	= $sMonth;
	
										$oServices->update($aTempData);
									}
								}								
																	
								if ( $val['is_dds'] > 0 ) {
									$nIDOffice 	= isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
									$nIDFirm	= $oFirm->getFirmByOffice($nIDOffice);
									$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);								
									$is_dds 	= 1;							
								} else {
									$nIDFirm 	= isset($aFirm['id']) ? $aFirm['id'] : 0;
									$sFirm 		= $oOffices->getFirmNameByIDOffice($val['id_office']);								
									$nIDService = $val['id_service'];		
									$aService	= $oServices->getService($val['id_service']);
									$nIDEarning	= isset($aService['id_earning']) ? $aService['id_earning'] : 0;
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
								
								if ( ($nCurrentSaldo + $real_sum) < 0 ) {
									throw new Exception("Недостатъчно салдо по фирма {$sFirm}!!!", DBAPI_ERR_INVALID_PARAM);
								}								

								// Наличност по сметка
								if ( !empty($account) ) {
									$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$account} LIMIT 1 FOR UPDATE");
									$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
								} else {
									throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
								}			
								
								if ( $nAccountState + $real_sum < 0 ) {
									throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
								}
															
								$aDataRows								= array();
								$aDataRows['id']						= 0;
								$aDataRows['id_order']					= $nIDOrder;
								$aDataRows['id_doc_row']				= $nIDRow;
								$aDataRows['id_office']					= $val['id_office'];
								$aDataRows['id_object']					= $val['id_object'];
								$aDataRows['id_service']				= $nIDService;
								$aDataRows['id_saldo']					= $nIDSaldo;
								$aDataRows['id_direction']				= 0;
								$aDataRows['id_bank']					= $account;
								$aDataRows['saldo_state']				= $nCurrentSaldo + $real_sum;
								$aDataRows['account_state']				= $nAccountState + $real_sum;
								$aDataRows['id_nomenclature_earning']	= $nIDEarning;
								$aDataRows['id_nomenclature_expense']	= 0;
								$aDataRows['month']						= $val['month'];
								$aDataRows['type']						= $val['type'];
								$aDataRows['paid_sum']					= $real_sum;
								$aDataRows['is_dds']					= $is_dds;		

								if ( !empty($real_sum) && (strlen($val['id']) == 13) ) {
									$oDBOrdersRow->update($aDataRows);
									
									// Обновяване на описа на документа
									$db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$real_sum}', paid_date = NOW() WHERE id = '{$nIDRow}' ");
									// Обновяване на салдата на фирмите
									$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$real_sum}' WHERE id = {$nIDSaldo} LIMIT 1");
									
									$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$real_sum}' WHERE id_bank_account = {$account} ");
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
				
				$err = "Получена грешка: ". $e->getMessage(). "\n";
				throw new Exception($err);
			}
			
			$oResponse->printResponse();
		}
	}

?>