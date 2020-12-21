<?php

	class ApiSaleDocInventory {
		
		public function result(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			
			$aA = array();
			if(!empty($Aa)) {
				throw new Exception("nothing happaning");
			}
			
			$oDBSalesDocs = new DBSalesDocs();
			$oDBSalesDocsRows = new DBSalesDocsRows();
			
			$aSaleDoc = array();
			$oDBSalesDocs->getRecord($nID,$aSaleDoc);
			
			if($aSaleDoc['doc_type'] != 'faktura') {
				$oResponse->setFormElement('form1','div_button',array("display" => "none"));
			}
			
			$oDBSalesDocsRows->getReport($oResponse,$nID);
				
			$oResponse->printResponse('Документ за продажба - Опис','sale_doc_inventory');
		}
		
		public function izvestie(DBResponse $oResponse) {	
			
			$nID = Params::get('nID',0);
			$aCheckboxes = Params::get('chk',array());
		
			$aIDs = array();
			foreach ($aCheckboxes as $key => $value) {
				if(!empty($value)) {
					$aIDs[] = $key;
				}
			}
			unset($key);unset($value);	
			
			if(empty($aIDs)) {
				throw new Exception("Няма избрани услуги");
			}
			
			$oDBSalesDocs = new DBSalesDocs();
			$oDBSalesDocsRows = new DBSalesDocsRows();
			$oDBSystem = new DBSystem();
			
			$aSystem = $oDBSystem->getRow();
			$nLastNumOrder = $aSystem['last_num_order'];
			
			$aSaleDoc = array();
			$oDBSalesDocs->getRecord($nID,$aSaleDoc);
			
			$aSaleDoc['id_credit_master'] = $aSaleDoc['id'];
			unset($aSaleDoc['id']);
			$aSaleDoc['doc_num'] = $nLastNumOrder + 1;
			$aSaleDoc['doc_status'] = 'proforma';
			$aSaleDoc['doc_type'] = 'kreditno izvestie';
			
			$aSaleDoc['total_sum'] = 0;
			$aSaleDoc['orders_sum'] = 0;
			$aSaleDoc['last_order_id'] = 0;
			$aSaleDoc['last_order_time'] = '0000-00-00 00:00:00';
			$aSaleDoc['note'] = '';
			
			$oDBSalesDocs->update($aSaleDoc);
			
			$oDBSystem->setLastNumOrder($nLastNumOrder + 1);
			
			$oResponse->setFormElement('form1','id_new_sale_doc',array(),$aSaleDoc['id']);
			
			$nTotalSum = 0;
			foreach ($aIDs as $nIDSaleDocRow) {
				$aSaleDocRow = array();
				$oDBSalesDocsRows->getRecord($nIDSaleDocRow,$aSaleDocRow);
				
				unset($aSaleDocRow['id']);
				$aSaleDocRow['id_sale_doc'] = $aSaleDoc['id'];
				$aSaleDocRow['single_price'] = -$aSaleDocRow['single_price'];
				$aSaleDocRow['total_sum'] = -$aSaleDocRow['total_sum'];
				$aSaleDocRow['paid_sum'] = 0;
				$aSaleDocRow['paid_date'] = '0000-00-00 00:00:00';
				
				$oDBSalesDocsRows->update($aSaleDocRow);
				
				$nTotalSum += $aSaleDocRow['total_sum'];
			}
			
			$aSaleDoc['total_sum'] = $nTotalSum;
			
			$oDBSalesDocs->update($aSaleDoc);
			
			
			/* Връщам старите падежи на услугите по обектите */
			
			$sIDs = implode(",",$aIDs);
			
			$aRowsFirstMonthsPaid = $oDBSalesDocsRows->getFirstPaidMonths($nID,$sIDs);
			$oDBSalesDocs->returnOldObjectLastPaids($aRowsFirstMonthsPaid);
			
			
			
			$oResponse->printResponse();
		}
		
	}

?>