<?php

	class ApiSalesPayOrders {
		
		public function load(DBResponse $oResponse) {
			
			$sIDs = Params::get("sIDs", '');
			$nDDS = Params::get("nDDS", 0);

			$aIDSaleDocs = explode( ",", $sIDs );
			
			$oDBSaleDocs 		= new DBSalesDocs();
			$oDBBankAccounts 	= new DBBankAccounts();
			
			$nSum = 0;
			//APILog::Log(0, $aIDSaleDocs);
			foreach ($aIDSaleDocs as $value) {
				$aSaleDoc = array();
				$oDBSaleDocs->getRecord($value, $aSaleDoc);
				$nSum += isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;  // * 5/6;
			}
			
			$aBankAccounts 	= array();
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$aBankAccounts 	= $oDBBankAccounts->getByPersonForOperate( $nIDPerson );
			
			$oResponse->setFormElement( 'form1', 'account' );
			$oResponse->setFormElementChild( 'form1', 'account', array("value" => "personal"), "Персонална" );
			
			foreach ($aBankAccounts as $key => $value) {
				$oResponse->setFormElementChild( 'form1', 'account', array("value" => $key), $value );
			}
			
			$oResponse->setFormElement( 'form1', 'sum', array(), $nSum );
			
			$oResponse->printResponse();
			
		}
		
		public function confirm( DBResponse $oResponse ) {
			
			$sIDs		= Params::get('sIDs', '');
			$account	= Params::get('account', '');
			$nSum		= Params::get('sum', 0);
			
			$aIDSaleDocs 	= explode(",", $sIDs);
			$sAccountType 	= $account == 'personal' ? 'person' : 'bank';
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$nSum 			= floatval($nSum);
			$nSum 			= round($nSum, 2);
			
			if ( empty($nSum) ) {
				throw new Exception("Въведете сума");
			}
			
			$oDBSaleDocs 		= new DBSalesDocs();
			$oDBSalesDocsRows 	= new DBSalesDocsRows();
			$oDBOrders 			= new DBOrders();
			$oDBSystem 			= new DBSystem();
			$oDBAccountStates 	= new DBAccountStates();
			$oSyncMoney 		= new DBSyncMoney();
			
			$nSalesDocsSum 		= 0;
			$tmpSum				= 0;
			
			foreach ( $aIDSaleDocs as $value ) {
				$aSaleDoc = array();
				$oDBSaleDocs->getRecord($value, $aSaleDoc);
				$tmpSum = isset($aSaleDoc['total_sum']) ? $aSaleDoc['total_sum'] : 0;
				$nSalesDocsSum += $tmpSum;
				
				// Pavel
				$sID 	= isset($aSaleDoc['id_schet']) ? $aSaleDoc['id_schet'] : 0;
				$sType 	= isset($aSaleDoc['doc_type']) ? $aSaleDoc['doc_type'] : "kvitanciq";
				$sPaid 	= isset($aSaleDoc['paid_type']) ? $aSaleDoc['paid_type'] : "cash";
				
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
				
				if ( !empty($sID) ) {
					$nIDSchet 	= intval( substr($sID, -7) );
					$mpTable 	= "mp".substr( $sID, 0, 6 );
					
					$aMoney 	= array();
					$nMoney 	= $oSyncMoney->getMoneyFromMP( $mpTable, $nIDSchet );
					$nMoney		= floatval($nMoney);
					$nMoney		= round($nMoney, 2);
					
					$nAskSum 	= floatval($nSalesDocsSum);
					$nAskSum 	= round($nAskSum, 2);	
					$nSaldo 	= $oSyncMoney->getSaldoById( 16 );
					
									
					if ( $nSum >= $nMoney ) {
						// Пълно плащане
						$aData 					= array();
						$aData['id'] 			= $nIDSchet;
						$aData['valid_sum'] 	= $nSum;
						$aData['data'] 			= time();
						$aData['confirm'] 		= 1;
						$aData['confirm_date'] 	= time();
						$aData['faktura_type'] 	= $nIDType;
						$aData['bank'] 			= $sPaid == "cash" ? 0 : 1;
						$aData['saldo'] 		= $nSum + $nSaldo;
					} else {
						$aData 					= array();
						$aData['id'] 			= $nIDSchet;
						$aData['valid_sum'] 	= ($nAskSum - $nMoney) + $nSum;
						$aData['data'] 			= time();
						$aData['confirm'] 		= 0;
						$aData['confirm_date'] 	= "0000-00-00 00:00:00";
						$aData['faktura_type'] 	= $nIDType;
						$aData['bank'] 			= $sPaid == "cash" ? 0 : 1;	
						$aData['saldo'] 		= $nSum + $nSaldo;					
					}
					
					$oSyncMoney->updateSchetMonth( $aData );	
					$oSyncMoney->increaseSaldo( 16, $nSum );				
				}
			}
			
			unset($value);
			
			$nSalesDocsSum 	= floatval($nSalesDocsSum);
			$nSalesDocsSum 	= round($nSalesDocsSum, 2);
			
			
			if ( $nSum > $nSalesDocsSum ) {
				throw new Exception("Въвели сте по голяма сума от дължимата");
			}
			
			foreach ($aIDSaleDocs as $value) {
				$aSaleDoc = array();
				$oDBSaleDocs->getRecord($value, $aSaleDoc);
				$aSystem = array();
				$aSystem = $oDBSystem->getRow();
				$nNumOrder = $aSystem['last_num_order'] + 1;
				
				$nOrderSum = ($tmpSum / $nSalesDocsSum) * $nSum;
				
				$aAccountState = array();
				
				if ($sAccountType == 'bank') {
					$aAccountState = $oDBAccountStates->getRow($sAccountType, 0, $account);
					$aAccountState['id_bank_account'] = $account;
				} else {
					$aAccountState = $oDBAccountStates->getRow($sAccountType, $nIDPerson);
					$aAccountState['id_person'] = $nIDPerson;
				}
				
				$aAccountState['account_type'] = $sAccountType;
				
				if ( isset($aAccountState['current_sum']) && !empty($aAccountState['current_sum']) ) {
					$aAccountState['current_sum'] += $nOrderSum;
				} else {
					$aAccountState['current_sum'] = $nOrderSum;
				}
				
				$oDBAccountStates->update($aAccountState);
				
				
				$aOrder 				= array();
				$aOrder['num'] 			= $nNumOrder;
				$aOrder['order_type'] 	= 'earning';
				$aOrder['order_date'] 	= time();
				$aOrder['order_sum'] 	= $nOrderSum;
				$aOrder['account_type'] = $sAccountType;
				$aOrder['id_person'] 	= $nIDPerson;
				$aOrder['account_sum'] 	= $aAccountState['current_sum'];
				
				if ( $sAccountType == 'bank' ) {
					$aOrder['bank_account_id'] = $account;
				}
				
				$aOrder['doc_id'] = $value;
				$aOrder['doc_type'] = 'sale';
			
				$oDBOrders->update($aOrder);
				$oDBSystem->setLastNumOrder($nNumOrder);
				
				$aSaleDoc['orders_sum'] = $nOrderSum;
				$aSaleDoc['last_order_id'] = $aOrder['id'];
				$aSaleDoc['last_order_time'] = time();
				
				$oDBSaleDocs->update($aSaleDoc);
				
				$aIDSaleDocRows = $oDBSalesDocsRows->getByIDSaleDoc($value);
				
				foreach ( $aIDSaleDocRows as $v ) {
					$v['paid_sum'] 	= $v['total_sum'] / $aSaleDoc['total_sum'] * $nOrderSum;
					$v['paid_date'] = time();
					$oDBSalesDocsRows->update($v);
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>