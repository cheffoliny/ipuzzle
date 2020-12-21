<?php

	class ApiSalaryFirms {
		
		public function load(DBResponse $oResponse) {
			
			$nIDSelectFirmFrom = Params::get('nIDSelectFirmFrom','');
			$nIDSelectFirmTo = Params::get('nIDSelectFirmTo','');
			
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
				
			$oResponse->setFormElement('form1', 'nIDFirmFrom', array(), '');
			$oResponse->setFormElement('form1', 'nIDFirmTo', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirmFrom', array_merge(array("value"=>'0')), "--Изберете--");
			$oResponse->setFormElementChild('form1', 'nIDFirmTo', array_merge(array("value"=>'0')), "--Изберете--");

			if(empty($nIDSelectFirmFrom)) {		
				foreach($aFirms as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDFirmFrom', array_merge(array("value"=>$key)), $value);
					$oResponse->setFormElementChild('form1', 'nIDFirmTo', array_merge(array("value"=>$key)), $value);
				}		
			} else {
				foreach($aFirms as $key => $value) {
					if($nIDSelectFirmFrom == $key) {
						$ch1 = array("selected" => "selecsted");
					} else {
						$ch1 = array();
					}
					
					if($nIDSelectFirmTo == $key) {
						$ch2 = array("selected" => "selecsted");
					} else {
						$ch2 = array();
					}
					
					$oResponse->setFormElementChild('form1', 'nIDFirmFrom', array_merge(array("value"=>$key),$ch1), $value);
					$oResponse->setFormElementChild('form1', 'nIDFirmTo', array_merge(array("value"=>$key),$ch2), $value);
				}	
			}
			$oResponse->printResponse();
		}
		
		public function result(DBResponse $oResponse) {

			$sAct = Params::get('sAct','1');
			$nIDFirmFrom = Params::get('nIDFirmFrom','');
			$nIDFirmTo = Params::get('nIDFirmTo','');
			$nYear = Params::get('year','');
			$nMonth = Params::get('month','');
			
			if( empty($nIDFirmFrom) ) {
				throw new Exception("Изберете фирма за 'служители от'",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nIDFirmTo) ) {
				throw new Exception("Изберете фирма на 'за сметка на'",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nYear) || $nYear < 2007 || $nYear > 2050 ) {
				throw new Exception("Въведете коректна година",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nMonth) || $nMonth < 1 || $nMonth > 12 ) {
				throw new Exception("Въведете коректен месец",DBAPI_ERR_INVALID_PARAM);
			}
			
			$nMonth = zero_padding($nMonth,2);
			
			$oDBSalary = new DBSalary();
			
			$aMonths = array(
				1 => "Януари",
				2 => "Февруари",
				3 => "Март",
				4 => "Април",
				5 => "Май",
				6 => "Юни",
				7 => "Юли",
				8 => "Август",
				9 => "Септември",
				10 => "Октомври",
				11 => "Ноември",
				12 => "Декември",
			);
			
			$aData = array();
			$aData['nIDFirmFrom'] = $nIDFirmFrom;
			$aData['nIDFirmTo'] = $nIDFirmTo;
			$aData['nMonth'] = $nYear . LPAD( $nMonth, 2, 0 );
			$nMonth = ( int ) $nMonth;
			$aData['sMonth'] = $aMonths[$nMonth];
			$nMonth += 1; if( $nMonth > 12 ){ $nMonth = 1; $nYear++; }
			$aData['nNextMonth'] = $nYear . LPAD( $nMonth, 2, 0 );
			$aData['sNextMonth'] = $aMonths[$nMonth];
			$aData['nLeaveMonth'] = $nMonth;
			$aData['nLeaveYear'] = $nYear;
			$aData['sMonthSQL'] = date( "Y-m-01", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
			
			if ($sAct == 1) {
				$oDBSalary->getReportFirms($aData,$oResponse);
			} else {
				$oDBSalary->getReportFirms2($aData,$oResponse);
			}
			
			$oResponse->printResponse();
		}
		
		
	}

?>