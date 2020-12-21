<?php

	class ApiStatesFilterTotals {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			$oDBFilters = new DBFilters();
			$oDBFiltersTotals = new DBFiltersTotals();
			
			$aFilter = $oDBFilters->getRecord($nID);
			$aFilterTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter($nID);
			
			if(!empty($aFilter['is_auto'])) {
				$oResponse->setFormElement('form1','auto',array("checked" => "checked"));
			}
			
			$nStartDate = strtotime($aFilter['auto_start_date']);

			if(!empty($nStartDate)) {
				$sStartDate = date('d.m.Y',$nStartDate);
			} else {
				$sStartDate = '';
			}
			
			if(in_array('total_count',$aFilterTotals)) {
				$oResponse->setFormElement('form1','total_count',array("checked" => "checked"));
			}
			
			if(in_array('total_price',$aFilterTotals)) {
				$oResponse->setFormElement('form1','total_price',array("checked" => "checked"));
			}
			
			$oResponse->setFormElement('form1','sFromDate',array(),$sStartDate);
			
			$oResponse->setFormElementAttributes('form1','sPeriod',array("value" => "{$aFilter['auto_period']}"));
			
			if(empty($aFilter['is_auto'])) {
				$oResponse->setFormElementAttributes('form1','total_count',array("disabled" => "true"));
				$oResponse->setFormElementAttributes('form1','total_price',array("disabled" => "true"));
				$oResponse->setFormElementAttributes('form1','sFromDate',array("disabled" => "true"));
				$oResponse->setFormElementAttributes('form1','sPeriod',array("disabled" => "true"));
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			
			$nID = Params::get('nID','0');
			$nAuto = Params::get('auto','0');
			$nTotalCount = Params::get('total_count','0');
			$nTotalPrice = Params::get('total_price','0');
			$sDateFrom = Params::get('sFromDate','');
			$sPeriod = Params::get('sPeriod','day');
			
			$nDateFrom = jsDateToTimestamp($sDateFrom);
			$sDateFrom = timestampToMysqlDateTime($nDateFrom);
			
			$oDBFilters = new DBFilters();
			$oDBFiltersTotals = new DBFiltersTotals();
			
			$aFilter = array();
			$aFilter['id'] = $nID;
			$aFilter['is_auto'] = $nAuto;
			$aFilter['auto_period'] = $sPeriod;
			$aFilter['auto_start_date'] = $sDateFrom;
			
			$oDBFilters->update($aFilter);
			
			
 			$oDBFiltersTotals->delFilterTotalsByIDFilter($aFilter['id']);
 			
 			$aTotals = array();
 			
 			$aTotals['total_count'] = $nTotalCount;
 			$aTotals['total_price'] = $nTotalPrice;
 			
 			foreach ($aTotals as $key => $value) {
 				if(!empty($value)) {
 					$aData = array();
 					$aData['id_filter'] = $aFilter['id'];
 					$aData['total_name'] = $key;
 					
 					$oDBFiltersTotals->update($aData);
 				}
 			}
		}
	}

?>